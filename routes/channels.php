<?php

use Illuminate\Support\Facades\Broadcast;

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

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('SendMessageTo.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('docuComment.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('review.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('attach.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('UpdateAccred.{auth}', function ($user, $auth) {
    return (int) $user->auth == (int) $auth;
});

Broadcast::channel('docupload.{id}', function ($user, $id) {
    return (int) $user->id == (int) $id;
});