<?php

namespace App\Application\ErrorLogs\Actions;

use App\Models\ErrorLog;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListErrorLogsAction
{
    /**
     * @return LengthAwarePaginator<int, ErrorLog>
     */
    public function execute(?string $level = null, int $perPage = 20): LengthAwarePaginator
    {
        return ErrorLog::query()
            ->when($level, fn ($query) => $query->where('level', $level))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage);
    }
}
