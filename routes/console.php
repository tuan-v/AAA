<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\User;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    User::query()
        ->where('status', User::STATUS_PENDING_EDIT)
        ->whereNotNull('resubmit_expires_at')
        ->where('resubmit_expires_at', '<=', now())
        ->update(['status' => User::STATUS_EXPIRED]);
})->dailyAt('00:15')->name('expire-returned-employee-requests')->withoutOverlapping();
