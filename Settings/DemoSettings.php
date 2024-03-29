<?php
namespace Modules\NsDemo\Settings;

use App\Services\Helper;
use App\Services\SettingsPage;
use Modules\NsDemo\Services\ForgeService;

class DemoSettings extends SettingsPage
{
    public function __construct()
    {   
        /**
         * @var ForgeService
         */
        $forgeService   =   app()->make( ForgeService::class );

        $this->labels       =   [
            'title'     =>  __( 'Demo Settings' ),
            'description'   =>  __( 'Configure Demo Settings' )
        ];

        $this->identifier   =   'ns-demo-settings';

        $this->form =   [
            'tabs'  =>  [
                'general'   =>  [
                    'label' =>  __( 'General' ),
                    'fields'    =>  [
                        [
                            'name'          =>  'nsdemo_forge_api',
                            'label'         =>  __( 'Forge API Key' ),
                            'type'          =>  'textarea',
                            'value'         =>  ns()->option->get( 'nsdemo_forge_api' ),
                            'description'   =>  __( 'Enter your Forge API Key.' )
                        ], [
                            'name'          =>  'nsdemo_servers',
                            'label'         =>  __( 'Select Demo Instances' ),
                            'type'          =>  'multiselect',
                            'options'       =>  Helper::toJsOptions( collect( $forgeService->getInstances() ), [ 'id', 'name' ]),
                            'value'         =>  ns()->option->get( 'nsdemo_servers' ),
                            'description'   =>  __( 'Those instances will be managed by the module. Be careful as the selected instances will be reset periodically.' )
                        ],
                    ]
                ]
            ]
        ];
    }
}