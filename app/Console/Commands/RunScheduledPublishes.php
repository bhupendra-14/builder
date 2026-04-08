<?php

namespace App\Console\Commands;

use App\Models\PublishHistory;
use App\Services\PublishService;
use Illuminate\Console\Command;

class RunScheduledPublishes extends Command
{
    protected $signature = 'publish:run-scheduled';

    protected $description = 'Execute any publish_histories rows whose scheduled_at has passed';

    public function handle(PublishService $publishService): int
    {
        $due = PublishHistory::where('status', 'pending')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->get();

        if ($due->isEmpty()) {
            $this->info('No scheduled publishes are due.');
            return self::SUCCESS;
        }

        $this->info("Running {$due->count()} scheduled publish(es)...");

        foreach ($due as $history) {
            try {
                $publishService->runScheduled($history);
                $this->info("  [{$history->id}] {$history->environment} — completed");
            } catch (\Throwable $e) {
                $this->error("  [{$history->id}] {$history->environment} — failed: {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
