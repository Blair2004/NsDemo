<?php
namespace Modules\NsDemo\Events;

use App\Classes\Output;
use Illuminate\Support\Facades\View;
use Modules\NsDemo\Crud\DemoInstancesCrud;
use Modules\NsDemo\Settings\DemoSettings;

/**
 * Register Events
**/
class DemoEvent
{
    public function dashboardMenus( $menus )
    {
        $menus  =   array_insert_before( $menus, 'modules', [
            'ns-demo-instances' =>  [
                'label' =>  __( 'Demo Instances' ),
                'childrens' =>  [
                    [
                        'label' =>  __( 'All Instances' ),
                        'href'  =>  ns()->route( 'ns-demo-instances' )
                    ], [
                        'label' =>  __( 'Create Instance' ),
                        'href'  =>  ns()->route( 'ns-demo-instances-create' )
                    ]
                ]
            ]
        ]);

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

    public function registerCrud( $identifier )
    {
        switch( $identifier ) {
            case 'ns-demo-instances': return DemoInstancesCrud::class; 
            default: return $identifier;
        }
    }
}