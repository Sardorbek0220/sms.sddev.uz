<?php

namespace App\Console\Commands;

use App\Services\CallRecordingService;
use Illuminate\Console\Command;

class RecordingsDownloadCommand extends Command
{
    protected $signature   = 'recordings:download {--batch=50 : max records per run}';
    protected $description = 'Download pending call recordings (for last 3 days) into local storage';

    public function handle(CallRecordingService $svc): int
    {
        $batch = (int) $this->option('batch');
        $stats = $svc->downloadPending($batch);
        $this->info(sprintf(
            '[recordings:download] attempted=%d success=%d failed=%d',
            $stats['attempted'], $stats['success'], $stats['failed']
        ));
        return 0;
    }
}
