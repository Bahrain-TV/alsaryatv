<?php

namespace App\Console\Commands;

use App\Models\Caller;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExportSeedData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:export-callers
                            {--limit=50 : Maximum number of records to export}
                            {--force : Overwrite existing seed file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export current caller data to seed CSV file (database/seeders/data/callers_seed.csv)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $force = $this->option('force');

        $seedPath = database_path('seeders/data/callers_seed.csv');

        // Check if file exists and force not set
        if (File::exists($seedPath) && !$force) {
            $this->error("Seed file already exists at: {$seedPath}");
            $this->warn("Use --force to overwrite existing file");
            return 1;
        }

        // Ensure directory exists
        $dir = dirname($seedPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            $this->info("Created directory: {$dir}");
        }

        // Get callers
        $query = Caller::query()->orderBy('created_at', 'desc');

        if ($limit > 0) {
            $query->limit($limit);
            $this->info("Exporting up to {$limit} callers...");
        } else {
            $this->info("Exporting all callers...");
        }

        $callers = $query->get();

        if ($callers->isEmpty()) {
            $this->warn("No callers found in database");

            // Create empty CSV with headers
            $csv = "Name,Phone,CPR,Status,Is Winner,Hits,Last Hit,Created At,Updated At\n";
            File::put($seedPath, $csv);

            $this->info("Created empty seed CSV: {$seedPath}");
            return 0;
        }

        // Open CSV file for writing
        $file = fopen($seedPath, 'w');

        // Write header
        fputcsv($file, [
            'Name',
            'Phone',
            'CPR',
            'Status',
            'Is Winner',
            'Hits',
            'Last Hit',
            'Created At',
            'Updated At'
        ]);

        // Write data
        $exported = 0;
        foreach ($callers as $caller) {
            fputcsv($file, [
                $caller->name,
                $caller->phone,
                $caller->cpr,
                $caller->status ?? 'active',
                $caller->is_winner ? 1 : 0,
                $caller->hits ?? 0,
                $caller->last_hit ? $caller->last_hit->format('Y-m-d H:i:s') : '',
                $caller->created_at ? $caller->created_at->format('Y-m-d H:i:s') : '',
                $caller->updated_at ? $caller->updated_at->format('Y-m-d H:i:s') : '',
            ]);
            $exported++;
        }

        fclose($file);

        $this->info("✅ Exported {$exported} callers to: {$seedPath}");
        $this->comment("📝 This file will be used by CallerSeeder when database is empty");

        return 0;
    }
}
