<?php
namespace Modules\NsDemo\Services;

use Illuminate\Support\Facades\Http;
use Modules\NsDemo\Jobs\FirstStepResetJob;

class ForgeService
{
    public function getInstances()
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . ns()->option->get( 'nsdemo_forge_api' ),
            'Accept' => 'application/json',
        ])->get('https://forge.laravel.com/api/v1/servers');

        return $response->json();
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
        $botService     =   app()->make( BotService::class );
        $sites          =   ns()->option->get( 'nsdemo_instances_sites', [] );

        foreach( $sites as $site ) {
            $server     =   explode( '-', $site )[ 0 ];
            $website    =   explode( '-', $site )[ 1 ];

            // send a command on laravel forge website
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . ns()->option->get( 'nsdemo_forge_api' ),
                'Accept' => 'application/json',
            ])->post('https://forge.laravel.com/api/v1/servers/' . $server . '/sites/' . $website . '/commands', [
                'command'   =>  'git reset --hard HEAD && git pull origin master'
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . ns()->option->get( 'nsdemo_forge_api' ),
                'Accept' => 'application/json',
            ])->post('https://forge.laravel.com/api/v1/servers/' . $server . '/sites/' . $website . '/commands', [
                'command'   =>  'php artisan ns:reset --mode hard'
            ]);

            FirstStepResetJob::dispatch( $server, $website )->delay( now()->addSeconds( 20 ) );
    
            $botService->sendMessage([
                'chat_id'   =>  env( 'NS_BULKIMPORT_TELEGRAM_GROUP' ),
                'text'      =>  sprintf(
                    __( 'website "%s" is being reset' ),
                    $this->getSiteName( $website )
                )
            ]);
        }
    }
}