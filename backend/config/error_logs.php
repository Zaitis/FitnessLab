<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Error Log Retention
    |--------------------------------------------------------------------------
    |
    | How long a captured error stays in the error_logs table before the
    | scheduled pruning job removes it. Bounds both table growth and how
    | long incidental personal data captured in a stack trace survives.
    | See docs/ARCHITECTURE.md's "log viewer is an attack surface" section.
    |
    */

    'retention_days' => env('ERROR_LOG_RETENTION_DAYS', 30),

];
