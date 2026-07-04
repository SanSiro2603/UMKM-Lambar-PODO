<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('stores.{storeId}', function ($user, $storeId) {
    return $user->isSeller() && $user->store && (int) $user->store->id === (int) $storeId;
});

Broadcast::channel('users.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

