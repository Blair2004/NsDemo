<?php
namespace Modules\NsDemo;

use Illuminate\Support\Facades\Event;
use App\Services\Module;
use Modules\NsDemo\Events\DemoEvent;
use TorMorten\Eventy\Facades\Eventy as Hook;

include_once( __DIR__ . '/vendor/autoload.php' );

class NsDemoModule extends Module
{
    public function __construct()
    {
        parent::__construct( __FILE__ );

        $this->events   =   new DemoEvent;

        Hook::addFilter( 'ns.before-login-fields', [ $this->events, 'demoAlert' ]);
    }
}