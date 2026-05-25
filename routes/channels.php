<?php

use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\BroadcastController;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/
// Kênh thông báo user cá nhân theo subdomain
Broadcast::channel('user.{id}.{sub}.notifications', function ($user, $id, $sub) {
    return app(BroadcastController::class)->authorizeUserChannel($user, $id, $sub);
});
// Kênh thông báo company theo subdomain
Broadcast::channel('company.{companyId}.{sub}.notifications', function ($user, $companyId, $sub) {
    return app(BroadcastController::class)->authorizeCompanyChannel($user, $companyId, $sub);
});

// Kênh thông báo user theo company và subdomain
Broadcast::channel('company.{companyId}.{id}.{sub}.notifications', function ($user, $companyId, $id, $sub) {
    return app(BroadcastController::class)->authorizeUserInCompanyChannel($user, $companyId, $id, $sub);
});

