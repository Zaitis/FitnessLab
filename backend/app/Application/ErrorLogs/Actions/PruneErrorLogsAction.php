<?php

namespace App\Application\ErrorLogs\Actions;

use App\Models\ErrorLog;

final class PruneErrorLogsAction
{
    public function execute(): int
    {
        return ErrorLog::where('created_at', '<', now()->subDays(config('error_logs.retention_days')))->delete();
    }
}
