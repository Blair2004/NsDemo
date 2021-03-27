<?php
namespace Modules\Demo;

use Illuminate\Support\Facades\Event;
use App\Services\Module;
use Modules\NsDemo\Events\DemoEvent;
use TorMorten\Eventy\Facades\Eventy as Hook;

class DemoModule extends Module
{
    public function __construct()
    {
        parent::__construct( __FILE__ );

        $this->events   =   new DemoEvent;

        Hook::addFilter( 'ns.before-login-fields', [ $this->events, 'demoAlert' ]);
    }
}