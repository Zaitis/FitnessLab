<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('error-logs:prune')->daily();

// Off-peak hour, chosen to be unlikely to interrupt a visitor mid-session
// on the demo account.
Schedule::command('demo:reset')->dailyAt('03:00');
