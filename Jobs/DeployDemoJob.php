<?php

namespace Modules\NsDemo\Jobs;

use App\Services\ModulesService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Modules\NsDemo\Services\BotService;
use Modules\NsDemo\Services\ForgeService;
use Throwable;

class DeployDemoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct( public $server, public $website, public $commands )
    {
        // ...
    }

    public function handle( BotService $botService, ForgeService $forgeService )
    {
        $botService->sendMessage([
            'chat_id'   =>  env( 'NS_BULKIMPORT_TELEGRAM_GROUP' ),
            'text'      =>  sprintf(
                __( 'website "%s" is being reset' ),
                $forgeService->getSiteName( $this->website )
            )
        ]);

        Http::withHeaders([
            'Authorization' => 'Bearer ' . ns()->option->get( 'nsdemo_forge_api' ),
            'Accept' => 'application/json',
        ])->post('https://forge.laravel.com/api/v1/servers/' . $this->server . '/sites/' . $this->website . '/commands', [
            'command'   =>  $this->commands
        ]);
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed( Throwable $exception)
    {
        $botService     =   app()->make( BotService::class );
        $botService->sendMessage([
            'chat_id'   =>  env( 'NS_BULKIMPORT_TELEGRAM_GROUP' ),
            'text'      =>  sprintf(
                __( 'Something went wrong 😓 for %s, while on FirstStepResetJob. Here is the error : %s.' ),
                url('/'),
                $exception->getMessage()
            )
        ]);
    }
}