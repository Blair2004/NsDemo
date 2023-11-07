<?php
namespace Modules\NsDemo\Providers;

use App\Classes\Hook;
use Illuminate\Support\ServiceProvider;
use Modules\NsDemo\Events\DemoEvent;
use Modules\NsDemo\Services\BotService;

class ModuleServiceProvider extends ServiceProvider
{
    public $event;

    public function register()
    {
        $this->app->singleton( BotService::class, function() {
            return new BotService;
        });

        $this->event   =   new DemoEvent;
        
        Hook::addFilter( 'ns-dashboard-menus', [ $this->event, 'dashboardMenus' ]);
        Hook::addFilter( 'ns.settings', [ $this->event, 'settingsPage' ], 10, 2 );
    }
}