<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('device:mqtt-listen --max-seconds=55')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground()
    ->when(static fn (): bool => config('services.mqtt.host') !== '');
