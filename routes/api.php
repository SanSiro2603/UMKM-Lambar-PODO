<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Xendit Webhook & External
|--------------------------------------------------------------------------
*/

// Webhook Xendit — exclude dari CSRF (di-handle di bootstrap/app.php)
Route::post('/webhook/xendit', [\App\Http\Controllers\WebhookController::class, 'handle'])
    ->name('api.webhook.xendit');