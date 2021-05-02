<?php

use Illuminate\Support\Facades\Route;
use Modules\Nsdemo\Http\Controllers\NsDemoController;

Route::post( '/telegram/bot/webhook', [ NsDemoController::class, 'telegramWebhook' ])->name( 'ns-demo-telegram-webhook' );