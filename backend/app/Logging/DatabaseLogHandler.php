<?php

namespace App\Logging;

use App\Models\ErrorLog;
use Illuminate\Support\Str;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

class DatabaseLogHandler extends AbstractProcessingHandler
{
    /**
     * Context key fragments redacted before a record ever reaches the
     * database — matched case-insensitively as substrings, at any nesting
     * depth. See docs/ARCHITECTURE.md's "log viewer is an attack surface"
     * section: redacting on write rather than on display means a later
     * change to the viewer can't leak what was never stored.
     *
     * Substring rather than exact match, because exact matching redacted
     * `password` while writing this application's own registration payload
     * key `password_confirmation` — the same secret — through in plaintext.
     * The same held for `api_key`, `access_token` and `X-XSRF-TOKEN`.
     */
    private const array SENSITIVE_KEY_FRAGMENTS = [
        'password', 'token', 'authorization', 'cookie', 'secret', 'api_key', 'apikey',
    ];

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
            if (is_string($key) && Str::contains(strtolower($key), self::SENSITIVE_KEY_FRAGMENTS)) {
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
