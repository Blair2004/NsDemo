<?php
namespace Modules\NsDemo\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . ns()->option->get( 'nsdemo_forge_api' ),
            'Accept' => 'application/json',
        ])->post( 'https://forge.laravel.com/api/v1/servers/' . $this->server . '/sites/' . $this->website . '/commands', [
            'command'   =>  collect([
                'php artisan ns:demo',
                'php artisan env:set NS_LOCK_MODULES --v=true'
            ])->join( ' && ' )
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
