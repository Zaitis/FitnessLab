<?php

namespace App\Http\Controllers\Admin;

use App\Application\ErrorLogs\Actions\ListErrorLogsAction;
use App\Http\Controllers\Controller;
use App\Models\ErrorLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ErrorLogController extends Controller
{
    public function index(Request $request, ListErrorLogsAction $action): JsonResponse
    {
        $request->validate([
            'level' => ['sometimes', 'string', 'in:'.implode(',', ErrorLog::LEVELS)],
        ]);

        return response()->json(
            $action->execute($request->string('level')->value() ?: null)
        );
    }
}
