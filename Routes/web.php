<?php

use Illuminate\Support\Facades\Route;
use Modules\Nsdemo\Http\Controllers\NsDemoController;
use Modules\NsDemo\Services\BotService;

Route::get( '/telegram/bot/setwebhook', [ NsDemoController::class, 'registerTelegramWebHook' ])->name( 'ns-demo-telegram-setwebhook' );
Route::get( '/telegram/bot/unsetwebhook', [ NsDemoController::class, 'unsetTelegramWebHook' ])->name( 'ns-demo-telegram-unsetwebhook' );