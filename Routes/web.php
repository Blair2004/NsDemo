<?php

use App\Http\Middleware\Authenticate;
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
        Route::get( 'demo-instances', [ NsDemoController::class, 'getInstances' ])->name( 'ns-demo-instances' );
        Route::get( 'demo-instances/edit/{instance}', [ NsDemoController::class, 'editInstances' ])->name( 'ns-demo-instances-edit' );
        Route::get( 'demo-instances/create', [ NsDemoController::class, 'createInstances' ])->name( 'ns-demo-instances-create' );
        Route::get( 'demo-instances/trigger/{instance}', [ NsDemoController::class, 'triggerInstances' ])->name( 'ns-demo-instances-trigger' );
    });
});