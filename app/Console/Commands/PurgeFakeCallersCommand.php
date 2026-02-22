<?php

namespace App\Console\Commands;

use App\Models\Caller;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurgeFakeCallersCommand extends Command
{
    protected $signature = 'callers:purge-fake
        {--until-cpr= : Delete callers from the beginning up to this CPR (inclusive)}
        {--dry-run : Show how many records would be deleted without deleting}
        {--force : Delete without confirmation}';

    protected $description = 'Delete fake callers from earliest record up to a specific CPR (inclusive)';

    public function handle(): int
    {
        $untilCpr = (string) ($this->option('until-cpr') ?? '');

        if ($untilCpr === '') {
            $this->error('Missing required option: --until-cpr');

            return self::FAILURE;
        }

        $target = Caller::query()
            ->where('cpr', $untilCpr)
            ->orderBy('id')
            ->first();

        if (! $target) {
            $this->error("CPR not found: {$untilCpr}");

            return self::FAILURE;
        }

        $cutoffId = $target->id;

        $query = Caller::query()->where('id', '<=', $cutoffId);
        $count = $query->count();

        if ($count === 0) {
            $this->warn('No records matched the cutoff criteria.');

            return self::SUCCESS;
        }

        $this->warn("Cutoff caller: #{$target->id} {$target->name} ({$target->cpr})");
        $this->line("Records to delete: {$count}");

        if ($this->option('dry-run')) {
            $this->info('Dry run complete. No records were deleted.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && $this->input->isInteractive()) {
            if (! $this->confirm('Proceed with deletion?', false)) {
                $this->warn('Deletion canceled.');

                return self::SUCCESS;
            }
        }

        DB::transaction(function () use ($cutoffId): void {
            Caller::query()->where('id', '<=', $cutoffId)->delete();
        });

        $this->info("Deleted {$count} callers successfully.");

        return self::SUCCESS;
    }
}
