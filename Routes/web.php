<?php

use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use Modules\Nsdemo\Http\Controllers\NsDemoController;

Route::get( '/telegram/bot/setwebhook', [ NsDemoController::class, 'registerTelegramWebHook' ])->name( 'ns-demo-telegram-setwebhook' );
Route::get( '/telegram/bot/unsetwebhook', [ NsDemoController::class, 'unsetTelegramWebHook' ])->name( 'ns-demo-telegram-unsetwebhook' );

Route::prefix( 'dashboard' )->group( function() {
    Route::middleware([
        SubstituteBindings::class,
        Authenticate::class,
    ])->group(function () {
        Route::get( 'settings/ns-demo-settings', [ NsDemoController::class, 'settings' ] )->name( 'ns-demo-settings' );
    });
});