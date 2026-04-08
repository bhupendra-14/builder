<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Run due scheduled publishes every minute.
Schedule::command('publish:run-scheduled')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();
