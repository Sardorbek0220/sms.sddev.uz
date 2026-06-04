<?php

namespace App\Console\Commands;

use App\Services\CallRecordingService;
use Illuminate\Console\Command;

class RecordingsPurgeCommand extends Command
{
    protected $signature   = 'recordings:purge {--days=3 : retention in days}';
    protected $description = 'Delete recordings older than N days from local storage';

    public function handle(CallRecordingService $svc): int
    {
        $days  = (int) $this->option('days');
        $stats = $svc->purgeOlderThan($days);
        $this->info(sprintf(
            '[recordings:purge] deleted=%d missing=%d (retention=%d days)',
            $stats['deleted'], $stats['missing'], $days
        ));
        return 0;
    }
}
