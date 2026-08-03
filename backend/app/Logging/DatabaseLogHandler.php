<?php

namespace App\Logging;

use App\Models\ErrorLog;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

class DatabaseLogHandler extends AbstractProcessingHandler
{
    /**
     * Context keys redacted before a record ever reaches the database —
     * matched case-insensitively, at any nesting depth. See
     * docs/ARCHITECTURE.md's "log viewer is an attack surface" section:
     * redacting on write rather than on display means a later change to
     * the viewer can't leak what was never stored.
     */
    private const array SENSITIVE_KEYS = ['password', 'token', 'authorization', 'cookie'];

    protected function write(LogRecord $record): void
    {
        ErrorLog::create([
            'level' => $record->level->getName(),
            'message' => $record->message,
            'context' => $this->redact($record->context),
        ]);
    }

    /**
     * @param  array<array-key, mixed>  $context
     * @return array<array-key, mixed>
     */
    private function redact(array $context): array
    {
        $redacted = [];

        foreach ($context as $key => $value) {
            if (is_string($key) && in_array(strtolower($key), self::SENSITIVE_KEYS, true)) {
                $redacted[$key] = '[redacted]';
            } elseif (is_array($value)) {
                $redacted[$key] = $this->redact($value);
            } else {
                $redacted[$key] = $value;
            }
        }

        return $redacted;
    }
}
