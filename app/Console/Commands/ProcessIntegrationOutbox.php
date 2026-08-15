<?php

namespace App\Console\Commands;

use App\Services\IntegrationOutboxService;
use Illuminate\Console\Command;

class ProcessIntegrationOutbox extends Command
{
    protected $signature = 'integration:outbox:process {--batch= : Maximum events to process (1-500)}';

    protected $description = 'Process pending integration outbox events with retry and dead-letter handling';

    public function handle(IntegrationOutboxService $outbox): int
    {
        $batchOption = $this->option('batch');
        $batchSize = $batchOption === null
            ? (int) config('integration.outbox.batch_size', 25)
            : (int) $batchOption;

        if ($batchSize < 1 || $batchSize > 500) {
            $this->error('The batch size must be between 1 and 500.');

            return self::INVALID;
        }

        $metrics = $outbox->processPendingEvents($batchSize);

        $this->components->info(sprintf(
            'Outbox processed=%d succeeded=%d failed=%d dead_lettered=%d',
            $metrics['processed'],
            $metrics['succeeded'],
            $metrics['failed'],
            $metrics['dead_lettered'],
        ));

        return self::SUCCESS;
    }
}
