<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Cache;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduler
Schedule::call(function () {
    // 1. Update streak for users (e.g. reset if they missed a day)
    // Placeholder logic for streak updates
    // App\Models\ReadingStatistic::where('updated_at', '<', now()->subDays(2))->update(['streak' => 0]);

    // 2. Clear old cache (e.g. daily Quran cache if necessary, though Redis handles TTL)
    Cache::flush(); // Or selectively flush specific keys

})->dailyAt('00:00')->name('nightly_updates')->withoutOverlapping();
