<?php
namespace Modules\NsDemo\Events;

use App\Classes\Output;
use Illuminate\Support\Facades\View;

/**
 * Register Events
**/
class DemoEvent
{
    public function demoAlert( Output $response ) 
    {
        $response->addOutput( View::make( 'Demo::login.notice' ) );
        return $response;
    }
}