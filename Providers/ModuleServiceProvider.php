<?php
namespace Modules\NsDemo\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\NsDemo\Services\BotService;

class ModuleServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton( BotService::class, function() {
            return new BotService;
        });
    }
}