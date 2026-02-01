<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\CheckMT5Heartbeat;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::job(new CheckMT5Heartbeat)
    ->everyMinute()
    ->withoutOverlapping();
