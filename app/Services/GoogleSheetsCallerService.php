<?php

namespace App\Services;

use Google\Client as Google_Client;
use Google\Service\Exception as Google_Service_Exception;
use Google\Service\Sheets as Google_Service_Sheets;
use Google\Service\Sheets\BatchUpdateValuesRequest;
use Google\Service\Sheets\ValueRange;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GoogleSheetsCallerService
{
    protected $client;

    protected $service;

    protected $spreadsheetId;

    protected $sheetName;

    protected $csvService;

    protected $batchProcessor = null;

    protected $rateLimitRetries = 0;

    protected const BATCH_SIZE = 100;

    public $backoffHandler = null;

    /**
     * Constructor
     */
    public function __construct($spreadsheetId, $sheetName, $credentialsPath)
    {
        $this->spreadsheetId = $spreadsheetId;
        $this->sheetName = $sheetName;

        $this->client = new Google_Client;
        $this->client->setAuthConfig($credentialsPath);
        $this->client->setScopes([Google_Service_Sheets::SPREADSHEETS]);

        $this->service = new Google_Service_Sheets($this->client);

        $this->csvService = new CsvExportService;

        $this->backoffHandler = function (callable $operation) {
            $retries = 0;
            $maxRetries = 5;
            $backoff = 1000; // Initial backoff in milliseconds

            while ($retries < $maxRetries) {
                try {
                    return $operation();
                    break;
                } catch (Google_Service_Exception $e) {
                }
            }
        };
    }

    /**
     * Set a custom backoff handler
     */
    public function setBackoffHandler(callable $handler)
    {
        $this->backoffHandler = $handler;
    }

    /**
     * Set a batch processor
     */
    public function setBatchProcessor(callable $processor)
    {
        $this->batchProcessor = $processor;
    }

    /**
     * Execute with backoff if handler is set
     */
    protected static function safeExecute(callable $operation)
    {
        return call_user_func(self::$backoffHandler, $operation);
    }

    /**
     * Process in batches if processor is set
     */
    protected function batchProcess(array $items, callable $processor, int $batchSize = 50)
    {
        if ($this->batchProcessor) {
            return call_user_func($this->batchProcessor, $items, $processor, $batchSize);
        }

        // Default implementation without batching
        return $processor($items);
    }

    /**
     * Sync callers with efficient API usage
     */
    public function syncCallers($lastSync = null)
    {
        // First generate CSV export
        try {
            $query = DB::table('callers');
            if ($lastSync) {
                $query->where(function ($q) use ($lastSync): void {
                    $q->where('updated_at', '>=', Carbon::parse($lastSync))
                        ->orWhere('created_at', '>=', Carbon::parse($lastSync));
                });
            }

            $callers = $query->get();

            // Generate CSV file
            $csvPath = $this->csvService->generate($callers);

            Log::info('CSV export generated for sync', [
                'path' => $csvPath,
                'records' => $callers->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to generate CSV for sync', ['error' => $e->getMessage()]);
        }
        $result = [
            'added_to_db' => [],
            'updated_in_db' => [],
            'added_to_sheet' => [],
            'updated_in_sheet' => [],
            'skipped' => [],
            'rate_limited_retries' => $this->rateLimitRetries,
        ];

        try {
            // Get data from sheet
            $sheetData = $this->safeExecute(function () {
                return $this->getAllRows();
            });

            // Get data from database
            // For incremental mode, only get records updated since last sync
            if ($lastSync) {
                $dbData = DB::table('callers')
                    ->where('updated_at', '>=', Carbon::parse($lastSync))
                    ->orWhere('created_at', '>=', Carbon::parse($lastSync))
                    ->get();
            } else {
                $dbData = DB::table('callers')->get();
            }

            // Process database-to-sheet sync first
            $this->syncDatabaseToSheet($dbData, $sheetData, $result);

            // Then process sheet-to-database sync
            $this->syncSheetToDatabase($sheetData, $result, $lastSync);

            return $result;
        } catch (\Exception $e) {
            Log::error('Sync error: '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }

    /**
     * Sync from database to sheet with batching
     */
    protected function syncDatabaseToSheet($dbData, $sheetData, &$result)
    {
        // Create a map of existing sheet data by phone number
        $sheetMap = [];
        foreach ($sheetData as $index => $row) {
            if ($index === 0 || empty($row[1])) {
                continue;
            } // Skip header or rows without phone
            $phone = $this->normalizePhoneNumber($row[1]);
            if (! empty($phone)) {
                $sheetMap[$phone] = [
                    'index' => $index,
                    'data' => $row,
                ];
            }
        }

        // Process database records that need to be added/updated in sheet
        $toAdd = [];
        $toUpdate = [];

        foreach ($dbData as $record) {
            $phone = $this->normalizePhoneNumber($record->phone);
            if (empty($phone)) {
                continue;
            }

            // Prepare row data
            $rowData = [
                $record->name ?? '',
                $record->phone ?? '',
                $record->region ?? '',
                $record->notes ?? '',
                // Add additional fields as needed
            ];

            if (isset($sheetMap[$phone])) {
                // Check if any data differs before updating
                $existingRow = $sheetMap[$phone]['data'];
                $needsUpdate = false;

                // Compare relevant fields
                if (($existingRow[0] ?? '') != ($record->name ?? '') ||
                    ($existingRow[2] ?? '') != ($record->region ?? '') ||
                    ($existingRow[3] ?? '') != ($record->notes ?? '')
                ) {
                    $needsUpdate = true;
                }

                if ($needsUpdate) {
                    $toUpdate[] = [
                        'row' => $sheetMap[$phone]['index'] + 1, // +1 for 1-based index in Sheets API
                        'data' => $rowData,
                    ];
                } else {
                    $result['skipped'][] = $phone;
                }
            } else {
                $toAdd[] = $rowData;
            }
        }

        // Process updates in batches
        if (! empty($toUpdate)) {
            $this->batchProcess($toUpdate, function ($batch) use (&$result) {
                $updates = [];

                foreach ($batch as $item) {
                    $updates[] = [
                        'range' => $this->sheetName.'!A'.$item['row'].':D'.$item['row'],
                        'values' => [$item['data']],
                    ];
                }

                // Execute batch update
                $body = new BatchUpdateValuesRequest([
                    'valueInputOption' => 'RAW',
                    'data' => $updates,
                ]);

                $this->safeExecute(function () use ($body): void {
                    $this->service->spreadsheets_values->batchUpdate($this->spreadsheetId, $body);
                });

                foreach ($batch as $item) {
                    $result['updated_in_sheet'][] = $item['data'][1];  // Phone number
                }

                return $batch;
            }, self::BATCH_SIZE);  // Update in configurable batch size
        }

        // Process additions
        if (! empty($toAdd)) {
            $this->batchProcess($toAdd, function ($batch) use (&$result) {
                $body = new ValueRange([
                    'values' => $batch,
                ]);

                $this->safeExecute(function () use ($body): void {
                    $this->service->spreadsheets_values->append(
                        $this->spreadsheetId,
                        $this->sheetName,
                        $body,
                        ['valueInputOption' => 'RAW']
                    );
                });

                foreach ($batch as $row) {
                    $result['added_to_sheet'][] = $row[1];  // Phone number
                }

                return $batch;
            }, self::BATCH_SIZE);  // Add in configurable batch size
        }
    }

    /**
     * Sync from sheet to database with batching
     */
    protected function syncSheetToDatabase($sheetData, &$result, $lastSync)
    {
        // Skip header row
        $dataRows = array_slice($sheetData, 1);

        // Create a map of existing DB records by phone
        $dbMap = [];
        $query = DB::table('callers');
        $dbRecords = $query->get();

        foreach ($dbRecords as $record) {
            $phone = $this->normalizePhoneNumber($record->phone);
            if (! empty($phone)) {
                $dbMap[$phone] = $record;
            }
        }

        // Process sheet rows
        $toAdd = [];
        $toUpdate = [];

        foreach ($dataRows as $row) {
            if (empty($row[1])) {
                continue;
            }  // Skip rows without phone

            $phone = $this->normalizePhoneNumber($row[1]);
            if (empty($phone)) {
                continue;
            }

            $data = [
                'name' => $row[0] ?? '',
                'phone' => $row[1],
                'region' => $row[2] ?? '',
                'notes' => $row[3] ?? '',
                'updated_at' => now(),
            ];

            if (isset($dbMap[$phone])) {
                $record = $dbMap[$phone];

                // Check if any fields differ
                $needsUpdate = false;
                if (
                    $record->name != ($data['name'] ?? '') ||
                    $record->region != ($data['region'] ?? '') ||
                    $record->notes != ($data['notes'] ?? '')
                ) {
                    $needsUpdate = true;
                }

                if ($needsUpdate) {
                    $data['id'] = $record->id;
                    $toUpdate[] = $data;
                } else {
                    $result['skipped'][] = $phone;
                }
            } else {
                $data['created_at'] = now();
                $toAdd[] = $data;
            }
        }

        // Process database updates in batches
        if (! empty($toUpdate)) {
            $this->batchProcess($toUpdate, function ($batch) use (&$result) {
                foreach ($batch as $record) {
                    DB::table('callers')
                        ->where('id', $record['id'])
                        ->update([
                            'name' => $record['name'],
                            'region' => $record['region'],
                            'notes' => $record['notes'],
                            'updated_at' => $record['updated_at'],
                        ]);

                    $result['updated_in_db'][] = $record['phone'];
                }

                return $batch;
            }, self::BATCH_SIZE);
        }

        // Process database inserts in batches
        if (! empty($toAdd)) {
            $this->batchProcess($toAdd, function ($batch) use (&$result) {
                DB::table('callers')->insert($batch);

                foreach ($batch as $record) {
                    $result['added_to_db'][] = $record['phone'];
                }

                return $batch;
            }, self::BATCH_SIZE);
        }
    }

    /**
     * Get winners from sheet (assumed to have a winner column)
     */
    public function getWinners(): array
    {
        $service = new GoogleSheetsCallerService(
            new Google_Service_Sheets(
                $this->client
            ),
            $this->client,
            $this->csvService
        );
        $this->spreadsheetId = config('google.spreadsheet_id') ?? '1476Go51xrfALX8EyJnV4Q0o8plHaCft7PKKWzlEcHZA';
        $this->sheetName = 'WINNERS';
        $sheetData = $service->getAllRows();
        if (empty($sheetData)) {
            return [];
        }

        $winners = [];

        foreach ($sheetData as $index => $row) {
            if ($index === 0 || empty($row[1])) {
                continue;
            } // Skip header or rows without phone

            // Check if the winner column is set to true
            if (strtolower($row[4] ?? '') === 'true') {
                $winners[] = [
                    'name' => $row[0] ?? '',
                    'phone' => $row[1],
                    'region' => $row[2] ?? '',
                    'notes' => $row[3] ?? '',
                ];
            }
        }

        return $winners;
    }

    /**
     * Update winner status in database
     */
    public function updateWinnerStatus(array $winners)
    {
        $updated = [];

        foreach ($winners as $winner) {
            $phone = $this->normalizePhoneNumber($winner['phone']);

            DB::table('callers')
                ->where('phone', 'like', '%'.$phone.'%')
                ->update([
                    'is_winner' => true,
                    'won_at' => now(),
                    'updated_at' => now(),
                ]);

            $updated[] = $phone;
        }

        return $updated;
    }

    /**
     * Normalize phone number for comparison
     */
    protected function normalizePhoneNumber($phone)
    {
        // Remove any non-numeric characters
        $phone = preg_replace('/\D/', '', $phone);

        // Ensure consistent format
        if (strlen($phone) > 0) {
            // Remove leading zeros
            $phone = ltrim($phone, '0');

            // Remove country code if present (e.g., 966 for Saudi Arabia)
            if (strlen($phone) > 9 && substr($phone, 0, 3) === '966') {
                $phone = substr($phone, 3);
            }
        }

        return $phone;
    }

    /**
     * Get all rows from the sheet with better error handling
     */
    public function getAllRows()
    {
        return $this->safeExecute(function () {
            try {
                $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $this->sheetName);

                return $response->getValues() ?: [];
            } catch (\Google_Service_Exception $e) {
                $error = json_decode($e->getMessage(), true);
                $errorMessage = $error['error']['message'] ?? $e->getMessage();

                if (strpos($errorMessage, 'Unable to parse range') !== false) {
                    throw new \Exception("Sheet '{$this->sheetName}' not found in the spreadsheet.");
                } else {
                    throw $e;
                }
            }
        });
    }

    /**
     * Get row count from the sheet
     */
    public function getRowCount()
    {
        $rows = $this->getAllRows();

        return count($rows);
    }

    /**
     * Test connection to Google Sheets API
     *
     * @return bool True if connection is successful
     *
     * @throws \Exception If connection fails
     */
    public function testConnection()
    {
        try {
            // Try a basic API call to verify credentials and access
            $this->service->spreadsheets->get($this->spreadsheetId);

            return true;
        } catch (\Google_Service_Exception $e) {
            $error = json_decode($e->getMessage(), true);
            $errorMessage = $error['error']['message'] ?? $e->getMessage();

            if (strpos($errorMessage, 'not found') !== false) {
                throw new \Exception("Spreadsheet not found. Check your spreadsheet ID: {$this->spreadsheetId}");
            } elseif (strpos($errorMessage, 'permission') !== false) {
                throw new \Exception('Permission denied. Ensure your credentials have access to the spreadsheet.');
            } else {
                throw new \Exception("Google API error: {$errorMessage}");
            }
        } catch (\Exception $e) {
            throw new \Exception("Connection test failed: {$e->getMessage()}");
        }
    }
}
