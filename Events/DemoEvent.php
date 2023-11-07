<?php
namespace Modules\NsDemo\Events;

use App\Classes\Output;
use Illuminate\Support\Facades\View;
use Modules\NsDemo\Settings\DemoSettings;

/**
 * Register Events
**/
class DemoEvent
{
    public function dashboardMenus( $menus )
    {
        if ( $menus[ 'settings' ] ) {
            $menus[ 'settings' ][ 'childrens' ][ 'nsdemo']    =   [
                'label'     =>  __( 'Demo Settings' ),
                'href'      =>  ns()->route( 'ns-demo-settings' ),
            ];
        }

        return $menus;
    }

    public function settingsPage( $settings, $identifier )
    {
        if ( $identifier == 'ns-demo-settings' ) {
            return new DemoSettings;
        }

        return $settings;
    }
}