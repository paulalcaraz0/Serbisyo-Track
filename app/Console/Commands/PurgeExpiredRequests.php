<?php

namespace App\Console\Commands;

use App\Services\RequestRetentionService;
use Illuminate\Console\Command;

class PurgeExpiredRequests extends Command
{
    protected $signature = 'requests:purge-expired {--dry-run : Count eligible records without deleting them}';

    protected $description = 'Purge closed service requests and private attachments after the configured retention period';

    public function handle(RequestRetentionService $retention): int
    {
        $result = $retention->purge((bool) $this->option('dry-run'));
        $verb = $result['dry_run'] ? 'would purge' : 'purged';

        $this->info(sprintf(
            'Retention cleanup %s %d request(s) and %d attachment record(s) through %s.',
            $verb,
            $result['request_count'],
            $result['attachment_count'],
            $result['cutoff_date'],
        ));

        return self::SUCCESS;
    }
}
