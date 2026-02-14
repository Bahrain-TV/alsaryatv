<?php

namespace App\Console\Commands;

use App\Models\Caller;
use App\Services\CsvExportService;
use App\Services\GoogleSheetsCallerService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ImportCallersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:callers:import
                            {file? : The path to the CSV file relative to storage/app/backups/callers/}
                            {--force : Force import without confirmation}
                            {--d|debug : Show detailed debug information}
                            {--l|list : List all CSV files for selection instead of picking latest}
                            {--w|winners : Import a winners only csv file}
                            {--bypass-auth : Bypass authorization checks when updating records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import callers from a CSV file';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        /**
         * MAIN FUNTIONALITY ****
         *
         * Reads a csv Winners csv file and marks them as winners in the database
         */
        if ($this->option('winners')) {
            $this->info('Importing winners only...');

            // get the latest csv file from /Users/aldoyh/Sites/alsaryatv-new/storage/app/private/winners/WINNERS-2025-03-11.csv

            $winners = $this->getWinnersFromGoogleSheet();
            $currentTotalkWinners = Caller::where('is_winner', true)->count();

            // Show totals of winners and file being imported on screen

            // Get confirmation
            if (! $this->option('force') && ! $this->confirm('This will import callers into the database. Continue?', true)) {
                $this->info('Import cancelled by user.');

                return 0;
            }

            return 0;
        }

        $this->info('Starting caller import process...');

        // Get the file path
        $filePath = $this->getFilePath();

        if (! $filePath) {
            return 1;
        }

        // Check if the file exists
        if (! Storage::exists($filePath)) {
            $this->error("File not found: {$filePath}");

            return 1;
        }

        // Read CSV headers to validate format
        $fullPath = Storage::path($filePath);
        $file = fopen($fullPath, 'r');
        $headers = fgetcsv($file);
        fclose($file);

        // Verify CSV structure
        if (! $this->validateCsvHeaders($headers)) {
            $this->error('Invalid CSV format. Required headers: name, phone, cpr');

            return 1;
        }

        // Process the file
        $this->processImport($fullPath, $headers);

        // Export the corrected version of the CSV file
        $this->exportCorrectedCsv();

        return 0;
    }

    /**
     * Import winners only from a CSV file
     */
    protected function importWinners(string $filePath, array $headers): void
    {
        // storage/app/WINNERS-2025-03-11.csv
        $this->info('Importing winners only...');
        $this->info('File: '.$filePath);
        $this->info('Headers: '.implode(', ', $headers));

        $file = file($filePath);
        $header = array_shift($file);
        $header = str_getcsv($header);
        $headerCount = count($header);

        // Log headers for debugging
        $this->info('Header: '.implode(', ', $header));
        $this->info('Processing winners...');
        $this->info('Total winners: '.count($file));

        // Check if required fields exist in headers (case-insensitive)
        $requiredFields = ['name', 'phone', 'cpr'];
        $headerMap = [];
        $missingFields = [];

        // Find corresponding header column for each required field (case-insensitive)
        foreach ($requiredFields as $field) {
            $found = false;
            foreach ($header as $headerField) {
                if (strcasecmp($field, trim($headerField)) === 0) {
                    $headerMap[$field] = $headerField;
                    $found = true;
                    break;
                }
            }

            if (! $found) {
                $missingFields[] = $field;
            }
        }

        if (! empty($missingFields)) {
            $this->error('Missing required fields in CSV: '.implode(', ', $missingFields));
            $this->info('Available fields: '.implode(', ', $header));
            $this->info('Please make sure your CSV has the required fields: name, phone, cpr');

            return;
        }

        $foundWinners = 0;
        $notFoundWinners = 0;
        $winnersFound = [];
        $winnersNotFound = [];
        $skippedRows = 0;

        $emailContent = '# القائمة المحدثة للفائزين'.PHP_EOL.PHP_EOL
           .'المجموع الكلي: '.count($file).PHP_EOL.PHP_EOL;

        foreach ($file as $line => $row) {
            try {
                $rowData = str_getcsv($row);

                // Handle row/header column count mismatch
                $rowCount = count($rowData);
                if ($rowCount !== $headerCount) {
                    $this->warn('Row '.($line + 2)." has $rowCount columns but headers have $headerCount columns");

                    // If we have more data columns than headers, truncate the row
                    if ($rowCount > $headerCount) {
                        $rowData = array_slice($rowData, 0, $headerCount);
                    }
                    // If we have fewer data columns than headers, pad with empty values
                    else {
                        $rowData = array_pad($rowData, $headerCount, '');
                    }
                }

                $rowArray = array_combine($header, $rowData);

                // Extract required field values using the header map
                $name = ! empty($headerMap['name']) ? $rowArray[$headerMap['name']] : '';
                $phone = ! empty($headerMap['phone']) ? $rowArray[$headerMap['phone']] : '';
                $cpr = ! empty($headerMap['cpr']) ? $rowArray[$headerMap['cpr']] : '';

                if (empty($name) || empty($phone) || empty($cpr)) {
                    $this->warn('Row '.($line + 2).' has missing required values');
                    $skippedRows++;

                    continue;
                }

                $mappedRow = [
                    'name' => $name,
                    'phone' => $phone,
                    'cpr' => $cpr,
                ];

                $this->info('Processing winner: '.$mappedRow['name'].' ('.$mappedRow['phone'].')');

                $caller = Caller::where('cpr', $mappedRow['cpr'])->first();
                if ($caller) {
                    $this->info('Winner found: '.$caller->name.' ('.$caller->phone.')');

                    try {
                        if ($this->option('bypass-auth')) {
                            // Use direct DB update to bypass model authorization
                            DB::table('callers')
                                ->where('id', $caller->id)
                                ->update(['is_winner' => true, 'updated_at' => now()]);
                        } else {
                            // Try normal Eloquent update
                            $caller->is_winner = true;
                            $caller->save();
                        }

                        $this->info('Winner updated: '.$caller->name.' ('.$caller->phone.')');
                        $foundWinners++;
                        $winnersFound[] = $caller;
                    } catch (\Exception $updateException) {
                        $this->error('Error updating winner '.$caller->name.': '.$updateException->getMessage());

                        // Try fallback method if not already using bypass
                        if (! $this->option('bypass-auth')) {
                            $this->warn('Attempting direct database update instead for '.$caller->name);
                            try {
                                DB::table('callers')
                                    ->where('id', $caller->id)
                                    ->update(['is_winner' => true, 'updated_at' => now()]);

                                $this->info('Winner updated with direct DB update: '.$caller->name);
                                $foundWinners++;
                                $winnersFound[] = $caller;
                            } catch (\Exception $fallbackException) {
                                $this->error('Fallback update failed for '.$caller->name.': '.$fallbackException->getMessage());
                                $skippedRows++;
                            }
                        } else {
                            $skippedRows++;
                        }
                    }
                } else {
                    $this->warn('Winner not found: '.$mappedRow['name'].' ('.$mappedRow['phone'].')');
                    $notFoundWinners++;
                    $winnersNotFound[] = $mappedRow;
                }
                $this->info('--------------------------------------------------');
            } catch (\Exception $e) {
                $this->error('Error processing row '.($line + 2).': '.$e->getMessage());
                $this->error('Row data: '.json_encode($rowData ?? []));
                $skippedRows++;
            }
        }

        // Build email content with separate sections
        $emailContent .= "\n## الفائزون الذين تم تحديثهم ($foundWinners):\n";
        foreach ($winnersFound as $winner) {
            $emailContent .= "\n- ".$winner->name.' ('.$winner->phone.')';
        }

        if ($notFoundWinners > 0) {
            $emailContent .= "\n\n## الفائزون الذين لم يتم العثور عليهم في قاعدة البيانات ($notFoundWinners):\n";
            foreach ($winnersNotFound as $winner) {
                $emailContent .= "\n- ".$winner['name'].' ('.$winner['phone'].')';
            }
        }

        // Display summary in console
        $this->info('Winners import summary:');
        $this->info('Total records processed: '.count($file));
        $this->info("Successfully updated: $foundWinners");
        if ($notFoundWinners > 0) {
            $this->warn("Records not found in database: $notFoundWinners");
        }
        if ($skippedRows > 0) {
            $this->error("Skipped rows due to errors: $skippedRows");
        }

        // send email to admin
        $this->info('Sending email to admin...');

        $subject = "Winners imported - Updated: $foundWinners, Not found: $notFoundWinners";
        mail('aldoyh.info@gmail.com', $subject, $emailContent, 'From: '.env('MAIL_FROM_ADDRESS'));
        // mail('alsaryatv@gmail.com', $subject, $emailContent, 'From: ' . env('MAIL_FROM_ADDRESS'));

        $this->info('Email sent to admin successfully.');
    }

    /**
     * Imports the Winners from Google Sheet - WINNERS Sheet
     */
    protected function importWinnersFromGoogleSheet(): void
    {
        $this->info('Importing winners from Google Sheet...');

        $winners = $this->getWinnersFromGoogleSheet();

        $this->info('Winners imported from Google Sheet successfully.');

        $this->info('Total winners: '.count($winners));

        $this->info('Starting caller import process...');

        // Get the file path
        $filePath = $this->getFilePath();

        if (! $filePath) {
            return;
        }

        // Check if the file exists
        if (! Storage::exists($filePath)) {
            $this->error("File not found: {$filePath}");

            return;
        }

        // Read CSV headers to validate format
        $fullPath = Storage::path($filePath);
        $file = fopen($fullPath, 'r');
        $headers = fgetcsv($file);
        fclose($file);

        // Verify CSV structure
        if (! $this->validateCsvHeaders($headers)) {
            $this->error('Invalid CSV format. Required headers: name, phone, cpr');

            return;
        }

        // Process the file
        $this->processImport($fullPath, $headers, $winners);

        // Export the corrected version of the CSV file
        $this->exportCorrectedCsv();
    }

    /**
     * Gets the winners from Google sheets
     */
    protected function getWinnersFromGoogleSheet(): array
    {
        $this->info('Getting winners from Google Sheet...');

        $spreadsheetId = config('google.spreadsheet_id') ?? '1476Go51xrfALX8EyJnV4Q0o8plHaCft7PKKWzlEcHZA';
        if (empty($spreadsheetId)) {
            $this->error('Google Spreadsheet ID is not configured in .env file');

            return [];
        }

        $range = 'WINNERS!A2:C';

        try {
            $winners = (new GoogleSheetsCallerService(
                '1476Go51xrfALX8EyJnV4Q0o8plHaCft7PKKWzlEcHZA',
                $range,
                config('google.credentials')
            ))->getAllRows();

            if (empty($winners)) {
                $this->warn('No winners found in the Google Sheet');

                return [];
            }

            // Validate the structure of the data
            foreach ($winners as $index => $winner) {
                if (! $winner) {
                    $this->warn('Winner record at index '.$index.' is empty');
                    unset($winners[$index]);

                    continue;
                }

                if (! isset($winner['name']) || ! isset($winner['phone']) || ! isset($winner['cpr'])) {
                    $this->warn('Winner record at index '.$index.' is missing required fields');
                    unset($winners[$index]);
                }
            }

            $this->info('Winners retrieved from Google Sheet successfully: '.count($winners).' winners found');

            return array_values($winners); // Re-index array after possible unsets

        } catch (\Exception $e) {
            $this->error('Failed to retrieve winners from Google Sheet: '.$e->getMessage());
            if ($this->option('debug')) {
                $this->error($e->getTraceAsString());
            }

            return [];
        }
    }

    /**
     * Get the file path from argument or prompt user to select
     */
    protected function getFilePath()
    {
        $filePath = $this->argument('file');

        if (! $filePath) {
            // List all CSV files in the specified directory
            $files = collect(Storage::files('backups/callers'))
                ->filter(function ($file) {
                    return Str::endsWith($file, '.csv');
                })
                ->values()
                ->all();

            if (empty($files)) {
                $this->error('No CSV files found in storage/app/backups/callers/');

                return null;
            }

            // If --list option is specified, show file selection
            if ($this->option('list')) {
                $filePath = $this->choice(
                    'Select a CSV file to import:',
                    $files,
                    0
                );
            } else {
                // Sort files by the grand total (third segment when split by underscore)
                $sortedFiles = collect($files)->sortByDesc(function ($file) {
                    $filename = basename($file);
                    $segments = explode('_', $filename);

                    // Check if we have at least 3 segments
                    if (count($segments) >= 3) {
                        // Extract the grand total value (third segment)
                        $grandTotal = $segments[2];
                        // Remove any non-numeric characters
                        $numericValue = preg_replace('/[^0-9]/', '', $grandTotal);

                        if (is_numeric($numericValue)) {
                            return (int) $numericValue;
                        }
                    }

                    // If format doesn't match, return 0
                    return 0;
                });

                $latestFile = $sortedFiles->first();

                // If no file with the expected format is found, fall back to most recent file
                if (! $latestFile) {
                    $latestFile = collect($files)->sortByDesc(function ($file) {
                        return Storage::lastModified($file);
                    })->first();
                }

                $this->info('Using file with highest grand total: '.$latestFile);
                $filePath = $latestFile;
            }
        } else {
            // If relative path provided, prepend the directory
            if (! Str::startsWith($filePath, 'backups/callers/')) {
                $filePath = 'backups/callers/'.$filePath;
            }
        }

        return $filePath;
    }

    /**
     * Validate CSV headers
     */
    protected function validateCsvHeaders(array $headers): bool
    {
        $requiredHeaders = ['name', 'phone', 'cpr'];

        // Debug: Print all headers exactly as they appear
        $this->debugLog('Original headers: '.implode(', ', $headers));

        // Convert headers to lowercase for case-insensitive comparison
        $headers = array_map('strtolower', $headers);

        foreach ($requiredHeaders as $required) {
            if (! in_array(strtolower($required), $headers)) {
                $this->error("Missing required header: $required");

                return false;
            }
        }

        // Log all available headers for debugging
        $this->debugLog('Found headers: '.implode(', ', $headers));

        return true;
    }

    /**
     * Process the import
     */
    protected function processImport(string $filePath, array $headers): void
    {
        $file = fopen($filePath, 'r');

        // Get headers (keys for the records)
        $headers = array_map('strtolower', fgetcsv($file));

        // Map headers to database fields
        $headerMap = $this->mapHeadersToFields($headers);

        // Debug log the headers and count
        $headerCount = count($headers);
        $this->debugLog('CSV Headers ('.$headerCount.'): '.implode(', ', $headers));

        // Count rows for progress bar (more efficiently)
        $this->info('Counting records...');
        $totalLines = 0;
        fseek($file, 0); // Reset to start of file
        while (! feof($file)) {
            if (fgets($file) !== false) {
                $totalLines++;
            }
        }
        $totalLines--; // Remove header line
        fseek($file, 0); // Reset again
        fgetcsv($file); // Skip header

        $this->info("Found {$totalLines} records to process");
        $bar = $this->output->createProgressBar($totalLines);
        $bar->start();

        $imported = 0;
        $skipped = 0;
        $errors = [];
        $batchSize = 100; // Process in batches of 100
        $recordsToProcess = [];
        $processedCount = 0;

        // Pre-fetch existing callers to avoid N+1 queries
        $this->info('Loading existing callers...');
        $existingByPhone = Caller::pluck('id', 'phone')->toArray();
        $existingByCpr = Caller::whereNotNull('cpr')->pluck('id', 'cpr')->toArray();

        // Start transaction for better performance
        DB::beginTransaction();

        try {
            while (($row = fgetcsv($file)) !== false) {
                // Handle row/header column count mismatch
                $rowCount = count($row);
                if ($rowCount !== $headerCount) {
                    $this->debugLog("Row has $rowCount columns but headers have $headerCount columns");

                    // If we have more data columns than headers, truncate the row
                    if ($rowCount > $headerCount) {
                        $row = array_slice($row, 0, $headerCount);
                        $this->debugLog('Truncated row to match header count');
                    }
                    // If we have fewer data columns than headers, pad with empty values
                    else {
                        $row = array_pad($row, $headerCount, '');
                        $this->debugLog('Padded row to match header count');
                    }
                }

                // Combine headers with values
                $record = array_combine($headers, $row);
                $this->debugLog('Processing record: '.json_encode($record));

                // Map record to database fields
                $record = $this->mapRecordToFields($record, $headerMap);

                // Validate record
                $validator = Validator::make($record, [
                    'name' => 'required|string|max:255',
                    'phone' => 'required|string|max:20',
                    'cpr' => 'nullable|string|max:20',
                ]);

                if ($validator->fails()) {
                    $errors[] = [
                        'record' => $record,
                        'errors' => $validator->errors()->toArray(),
                    ];
                    $this->warn('Validation failed for row: '.json_encode($record));
                    $skipped++;
                    $bar->advance();

                    continue;
                }

                // Check if caller exists using our pre-fetched data
                $existingId = null;
                if (isset($existingByPhone[$record['phone']])) {
                    $existingId = $existingByPhone[$record['phone']];
                } elseif (! empty($record['cpr']) && isset($existingByCpr[$record['cpr']])) {
                    $existingId = $existingByCpr[$record['cpr']];
                }

                $recordData = [
                    'name' => $record['name'],
                    'phone' => $record['phone'],
                    'cpr' => $record['cpr'] ?? null,
                    'is_winner' => $this->parseBooleanField($record, 'is_winner', false),
                    'status' => $this->parseStatusField($record['status'] ?? 'active'),
                    'notes' => $record['notes'] ?? null,
                ];

                if ($existingId) {
                    $recordData['id'] = $existingId;
                    $recordData['updated_at'] = now();
                } else {
                    $recordData['hits'] = isset($record['hits']) ? intval($record['hits']) : 0;
                    $recordData['last_hit'] = $this->parseDateSafely($record, 'last_hit');
                    $recordData['created_at'] = $this->parseDateSafely($record, 'created_at');
                    $recordData['updated_at'] = now();
                }

                $recordsToProcess[] = $recordData;
                $processedCount++;
                $imported++;
                $bar->advance();

                // Process in batches to reduce memory usage
                if (count($recordsToProcess) >= $batchSize || feof($file)) {
                    $this->processBatch($recordsToProcess, $existingByPhone, $existingByCpr);
                    $recordsToProcess = [];
                }
            }

            // Process any remaining records
            if (! empty($recordsToProcess)) {
                $this->processBatch($recordsToProcess, $existingByPhone, $existingByCpr);
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Import failed: '.$e->getMessage());
            throw $e;
        }

        fclose($file);
        $bar->finish();
        $this->newLine(2);

        // Report results
        $this->info('Import complete!');
        $this->info('Total records processed: '.($imported + $skipped));
        $this->info("Successfully imported: {$imported}");

        if ($skipped > 0) {
            $this->warn("Skipped records: {$skipped}");

            if ($this->confirm('Do you want to see the errors?', false)) {
                $this->displayErrors($errors);
            }
        }
    }

    /**
     * Map CSV headers to database fields dynamically
     */
    protected function mapHeadersToFields(array $headers): array
    {
        $headerMap = [];

        // Define common variations of header names
        $headerVariations = [
            'name' => ['name', 'full_name', 'caller_name', 'customer_name', 'username', 'fullname', 'caller', 'full name', 'caller name'],
            'phone' => ['phone', 'phone_number', 'mobile', 'cell', 'telephone', 'contact', 'phonenumber', 'caller_phone', 'customer_phone', 'phone number', 'tel', 'mobile number', 'contact number'],
            'cpr' => ['cpr', 'id', 'id_number', 'national_id', 'identification', 'identitynumber', 'caller_id', 'id number', 'national id', 'identity', 'identity number', 'personal id'],
            'hits' => ['hits', 'attempts', 'calls', 'call_count', 'entries', 'call count', 'entry count', 'total calls'],
            'last_hit' => ['last_hit', 'lastcall', 'last_call', 'last_attempt', 'last_entry', 'last_update', 'last call', 'last attempt'],
            'status' => ['status', 'state', 'active', 'is_active', 'enabled', 'caller_status', 'caller status', 'account status'],
            'notes' => ['notes', 'comment', 'comments', 'description', 'details', 'additional_info', 'note', 'additional info'],
            'is_winner' => ['is_winner', 'winner', 'won', 'has_won', 'iswinner', 'is winner', 'has won', 'win status'],
            'created_at' => ['created_at', 'created', 'date_created', 'creation_date', 'registered_at', 'registration_date', 'created at', 'registered at'],
            'updated_at' => ['updated_at', 'updated', 'modified', 'last_updated', 'modification_date', 'date_modified', 'updated at', 'last updated'],
        ];

        // Match headers to database fields using variations (case-insensitive)
        foreach ($headers as $header) {
            $matched = false;
            $headerLower = strtolower(trim($header));

            foreach ($headerVariations as $dbField => $variations) {
                foreach ($variations as $variation) {
                    if (strcasecmp($headerLower, $variation) === 0) {
                        $headerMap[$header] = $dbField;
                        $matched = true;
                        $this->debugLog("Mapped header '$header' to database field '$dbField'");
                        break 2;
                    }
                }
            }

            if (! $matched) {
                // For unmatched headers, keep them as-is
                $headerMap[$header] = $header;
                $this->debugLog("Couldn't map header '$header' to a known database field");
            }
        }

        return $headerMap;
    }

    /**
     * Map record to database fields
     */
    protected function mapRecordToFields(array $record, array $headerMap): array
    {
        $mappedRecord = [];
        foreach ($record as $key => $value) {
            if (isset($headerMap[$key])) {
                $mappedRecord[$headerMap[$key]] = $value;
            }
        }

        return $mappedRecord;
    }

    /**
     * Process a batch of records
     */
    protected function processBatch(array $records, array &$existingByPhone, array &$existingByCpr): void
    {
        // Split records into updates and inserts
        $updates = [];
        $inserts = [];

        foreach ($records as $record) {
            if (isset($record['id'])) {
                $id = $record['id'];
                unset($record['id']);
                $updates[$id] = $record;
            } else {
                $inserts[] = $record;
            }
        }

        // Perform batch updates one at a time to handle potential duplicate CPRs
        foreach ($updates as $id => $data) {
            try {
                if ($this->option('bypass-auth')) {
                    // Use direct DB update to bypass model authorization
                    DB::table('callers')->where('id', $id)->update($data);
                } else {
                    Caller::where('id', $id)->update($data);
                }
            } catch (\Exception $e) {
                // Check if this is a duplicate CPR error
                $isDuplicateCpr = $e->getCode() == 23000 && (
                    strpos($e->getMessage(), 'callers_cpr_unique') !== false ||
                    strpos($e->getMessage(), 'callers.cpr') !== false ||
                    strpos($e->getMessage(), 'UNIQUE constraint failed: callers.cpr') !== false
                );

                if ($isDuplicateCpr) {
                    // Get the CPR that's duplicated
                    $cpr = $data['cpr'] ?? null;
                    if ($cpr) {
                        $this->warn("Found duplicate CPR: $cpr - Incrementing hits instead of updating");
                        // Increment hits for the user with this phone number
                        Caller::where('id', $id)->increment('hits', 1, [
                            'last_hit' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                } elseif (strpos($e->getMessage(), 'Unauthorized caller update') !== false) {
                    $this->warn("Authorization error for caller ID $id - Attempting direct DB update");
                    try {
                        // Fallback to direct DB update
                        DB::table('callers')->where('id', $id)->update($data);
                    } catch (\Exception $fallbackException) {
                        $this->error('Fallback update failed: '.$fallbackException->getMessage());
                        throw $fallbackException;
                    }
                } else {
                    // Re-throw any other database exceptions
                    throw $e;
                }
            }
        }

        // Perform batch inserts with duplicate CPR handling
        if (! empty($inserts)) {
            foreach ($inserts as $record) {
                try {
                    if ($this->option('bypass-auth')) {
                        // Use direct DB insert
                        $id = DB::table('callers')->insertGetId($record);

                        // Update our cached lookups
                        $existingByPhone[$record['phone']] = $id;
                        if (! empty($record['cpr'])) {
                            $existingByCpr[$record['cpr']] = $id;
                        }
                    } else {
                        $caller = new Caller($record);
                        $caller->save();

                        // Update our cached lookups
                        $existingByPhone[$record['phone']] = $caller->id;
                        if (! empty($record['cpr'])) {
                            $existingByCpr[$record['cpr']] = $caller->id;
                        }
                    }
                } catch (\Exception $e) {
                    if (strpos($e->getMessage(), 'Unauthorized caller') !== false) {
                        $this->warn('Authorization error during insert - Attempting direct DB insert');
                        try {
                            // Fallback to direct DB insert
                            $id = DB::table('callers')->insertGetId($record);

                            // Update our cached lookups
                            $existingByPhone[$record['phone']] = $id;
                            if (! empty($record['cpr'])) {
                                $existingByCpr[$record['cpr']] = $id;
                            }
                        } catch (\Exception $fallbackException) {
                            $this->error('Fallback insert failed: '.$fallbackException->getMessage());
                            throw $fallbackException;
                        }
                    } elseif ($e->getCode() == 23000 && (
                        strpos($e->getMessage(), 'callers_cpr_unique') !== false ||
                        strpos($e->getMessage(), 'callers.cpr') !== false ||
                        strpos($e->getMessage(), 'UNIQUE constraint failed: callers.cpr') !== false
                    )) {
                        // Check if this is a duplicate CPR error
                        $cpr = $record['cpr'] ?? null;
                        if ($cpr) {
                            $this->warn("Found duplicate CPR during insert: $cpr - Incrementing hits");
                            // Find the caller with this CPR and increment hits
                            $existingCaller = Caller::where('cpr', $cpr)->first();
                            if ($existingCaller) {
                                $existingCaller->increment('hits', 1, [
                                    'last_hit' => now(),
                                    'updated_at' => now(),
                                ]);
                            }
                        }
                    } else {
                        // Re-throw any other database exceptions
                        throw $e;
                    }
                }
            }
        }
    }

    /**
     * Display import errors
     */
    protected function displayErrors(array $errors): void
    {
        $this->table(
            ['Record', 'Error Message'],
            collect($errors)->map(function ($error) {
                return [
                    json_encode($error['record']),
                    implode(', ', array_map(function ($errorMessages) {
                        return implode(', ', $errorMessages);
                    }, $error['errors'])),
                ];
            })
        );
    }

    /**
     * Parse boolean fields from various formats (true/false strings, 0/1, yes/no)
     */
    protected function parseBooleanField(array $record, string $field, bool $default = false): bool
    {
        if (! isset($record[$field])) {
            return $default;
        }

        $value = $record[$field];

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (bool) $value;
        }

        $strVal = strtolower(trim($value));

        return in_array($strVal, ['true', 'yes', 'y', '1']);
    }

    /**
     * Export the corrected version of the CSV file
     */
    protected function exportCorrectedCsv(): void
    {
        $this->info('Exporting corrected version of the CSV file...');

        $callers = Caller::all();
        $csvService = new CsvExportService;
        $csvService->generate($callers);

        $this->info('Corrected CSV file exported successfully.');
    }

    /**
     * Parse a date field safely, handling various formats and errors
     */
    protected function parseDateSafely(array $record, string $field): ?Carbon
    {
        if (empty($record[$field])) {
            return now();
        }

        $value = $record[$field];

        // If it's already a Carbon instance, return it
        if ($value instanceof Carbon) {
            return $value;
        }

        // Skip parsing for non-date values
        if (in_array(strtolower($value), ['active', 'inactive', 'pending', 'blocked', '0', '1', 'yes', 'no', 'true', 'false'])) {
            return now();
        }

        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            $this->debugLog("Could not parse date '{$value}' for field '{$field}'. Using current date instead.");

            return now();
        }
    }

    /**
     * Parse status field to ensure it's a valid status
     */
    protected function parseStatusField($status): string
    {
        if (empty($status)) {
            return 'active';
        }

        // Normalize status value
        $status = strtolower(trim($status));

        // Map numeric values to string status
        if ($status === '0' || $status === 0) {
            return 'inactive';
        }

        if ($status === '1' || $status === 1) {
            return 'active';
        }

        // Map common status values
        $validStatuses = [
            'active' => 'active',
            'inactive' => 'inactive',
            'pending' => 'pending',
            'blocked' => 'blocked',
            'true' => 'active',
            'false' => 'inactive',
            'yes' => 'active',
            'no' => 'inactive',
        ];

        return $validStatuses[$status] ?? 'active';
    }

    /**
     * Debug log helper
     */
    protected function debugLog($message): void
    {
        if ($this->option('debug')) {
            $this->info('[DEBUG] '.$message);
        }
    }
}
