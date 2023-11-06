<?php
namespace Modules\NsDemo\Events;

use App\Classes\Output;
use Illuminate\Support\Facades\View;

/**
 * Register Events
**/
class DemoEvent
{
    public function header( Output $response ) 
    {
        $response->addOutput( View::make( 'NsDemo::login.header' ) );
    }

    public function footer( Output $response ) 
    {
        $response->addOutput( View::make( 'NsDemo::login.footer' ) );
    }
}