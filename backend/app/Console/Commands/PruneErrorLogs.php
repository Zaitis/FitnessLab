<?php

namespace App\Console\Commands;

use App\Application\ErrorLogs\Actions\PruneErrorLogsAction;
use Illuminate\Console\Command;

class PruneErrorLogs extends Command
{
    protected $signature = 'error-logs:prune';

    protected $description = 'Delete error_logs entries older than the configured retention window';

    public function handle(PruneErrorLogsAction $action): void
    {
        $deleted = $action->execute();

        $this->info("Pruned {$deleted} error log entries.");
    }
}
