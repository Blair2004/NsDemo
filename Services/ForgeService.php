<?php
namespace Modules\NsDemo\Services;

use Illuminate\Support\Facades\Http;
use Modules\NsDemo\Jobs\FirstStepResetJob;
use Exception;
use Modules\NsDemo\Jobs\DeployDemoJob;
use Modules\NsDemo\Models\DemoInstance;

class ForgeService
{
    public function getInstances()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . ns()->option->get( 'nsdemo_forge_api' ),
                'Accept' => 'application/json',
            ])->get('https://forge.laravel.com/api/v1/servers');

            return $response->json()[ 'servers' ];
        } catch( Exception $exception ) {
            return [];
        }
    }

    public function getSites()
    {
        $servers    =   ns()->option->get( 'nsdemo_servers' );
        
        if ( $servers !== null ) {
            $sites      =   [];

            foreach( $servers as $serverid ) {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . ns()->option->get( 'nsdemo_forge_api' ),
                    'Accept' => 'application/json',
                ])->get('https://forge.laravel.com/api/v1/servers/' . $serverid . '/sites');
        
                $sites[ $serverid ]  =   $response->json()[ 'sites' ];
            }

            return $sites;
        }

        return [];
    }

    public function getSiteName( $id )
    {
        $servers    =   ns()->option->get( 'nsdemo_servers' );
        
        if ( $servers !== null ) {
            $sites      =   [];

            foreach( $servers as $serverid ) {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . ns()->option->get( 'nsdemo_forge_api' ),
                    'Accept' => 'application/json',
                ])->get('https://forge.laravel.com/api/v1/servers/' . $serverid . '/sites');
        
                $sites[ $serverid ]  =   $response->json()[ 'sites' ];
            }

            return collect( $sites )->flatten(1)->firstWhere( 'id', $id )[ 'name' ];
        }

        return 'Unknown';
    }

    public function resetSelectedWebsites()
    {
        $instances  =   DemoInstance::get();

        $instances->each( fn( $instance ) => $this->triggerInstances( $instance ) );
    }

    public function triggerInstances( DemoInstance $instance )
    {
        $forgeInstances     =   json_decode( $instance->forge_id );
        foreach( ( array ) $forgeInstances as $site ) {
            $server     =   explode( '-', $site )[ 0 ];
            $website    =   explode( '-', $site )[ 1 ];

            DeployDemoJob::dispatch( $server, $website, $instance->commands );
        }
    }
}