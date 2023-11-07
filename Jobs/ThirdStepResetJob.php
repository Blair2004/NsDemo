<?php
namespace Modules\NsDemo\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Throwable;

class ThirdStepResetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct( public $server, public $website )
    {
        // ...
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $commands   =   $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . ns()->option->get( 'nsdemo_forge_api' ),
            'Accept' => 'application/json',
        ])->get( 'https://forge.laravel.com/api/v1/servers/' . $this->server . '/sites/' . $this->website . '/commands' );

        $commands   =   collect( $commands->json()[ 'commands' ] )->reverse();

        /**
         * frist NsGastro enabling command
         */
        $gastroEnablingCommand  =   $commands->filter( fn( $command ) => $command[ 'command' ] === 'php artisan modules:enable NsPrintAdapter && php artisan modules:enable NsGastro' )->values()->first();
        $multiStoreEnablingCommand  =   $commands->filter( fn( $command ) => $command[ 'command' ] === 'php artisan modules:enable NsMultiStore' )->values()->first();

        /**
         * If either Gastro, Multistore and NsPrintAdapter are missing
         * we'll enable the default demo.
         */
        $gastroCommandOutput  =   $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . ns()->option->get( 'nsdemo_forge_api' ),
            'Accept' => 'application/json',
        ])->get( 'https://forge.laravel.com/api/v1/servers/' . $this->server . '/sites/' . $this->website . '/commands/' . $gastroEnablingCommand[ 'id' ] )->json();

        $multiStoreCommandOutput  =   $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . ns()->option->get( 'nsdemo_forge_api' ),
            'Accept' => 'application/json',
        ])->get( 'https://forge.laravel.com/api/v1/servers/' . $this->server . '/sites/' . $this->website . '/commands/' . $gastroEnablingCommand[ 'id' ] )->json();

        if ( $gastroEnablingCommand->isEmpty() && $multiStoreEnablingCommand->isEmpty() ) {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . ns()->option->get( 'nsdemo_forge_api' ),
                'Accept' => 'application/json',
            ])->post( 'https://forge.laravel.com/api/v1/servers/' . $this->server . '/sites/' . $this->website . '/commands', [
                'command'   =>  'php artisan ns:reset --mode grocery --with-procurements --with-sales'
            ]);
        }


        if ( ! $gastroEnablingCommand->isEmpty() && $multiStoreEnablingCommand->isEmpty() ) {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . ns()->option->get( 'nsdemo_forge_api' ),
                'Accept' => 'application/json',
            ])->post( 'https://forge.laravel.com/api/v1/servers/' . $this->server . '/sites/' . $this->website . '/commands', [
                'command'   =>  'php artisan demo:reset gastro'
            ]);
        }

        if ( ! $gastroEnablingCommand->isEmpty() && ! $multiStoreEnablingCommand->isEmpty() ) {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . ns()->option->get( 'nsdemo_forge_api' ),
                'Accept' => 'application/json',
            ])->post( 'https://forge.laravel.com/api/v1/servers/' . $this->server . '/sites/' . $this->website . '/commands', [
                'command'   =>  'php artisan demo:reset multistore'
            ]);
        }
        
        /**
         * Lock the modules management to prevent any to be downloaded.
         */
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . ns()->option->get( 'nsdemo_forge_api' ),
            'Accept' => 'application/json',
        ])->post( 'https://forge.laravel.com/api/v1/servers/' . $this->server . '/sites/' . $this->website . '/commands', [
            'command'   =>  'php artisan env:set NS_LOCK_MODULES --v=true'
        ]);
    }

    public function fail( Throwable $exception ) 
    {
        $botService     =   app()->make( BotService::class );
        $botService->sendMessage([
            'chat_id'   =>  env( 'NS_BULKIMPORT_TELEGRAM_GROUP' ),
            'text'      =>  sprintf(
                __( 'Something went wrong 😓 for %s, while on SecondStepJob. Here is the error : %s.' ),
                url('/'),
                $exception->getMessage()
            )
        ]);
    }
}
