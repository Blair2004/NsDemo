<?php
namespace Modules\NsDemo\Jobs;

use App\Jobs\ComputeDashboardMonthReportJob;
use App\Jobs\ComputeDayReportJob;
use App\Models\Role;
use App\Models\User;
use App\Services\DemoService;
use App\Services\ModulesService;
use App\Services\ResetService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Modules\NsDemo\Console\Commands\ReportCommand;
use Modules\NsDemo\Services\BotService;
use Modules\NsDemo\Services\ForgeService;
use Modules\NsGastro\Services\RestaurantDemoService;
use Modules\NsMultiStore\Models\Store;
use Throwable;

class SecondStepJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct( public $server, public $website )
    {
        // ...
    }
    
    public function handle()
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . ns()->option->get( 'nsdemo_forge_api' ),
            'Accept' => 'application/json',
        ])->post( 'https://forge.laravel.com/api/v1/servers/' . $this->server . '/sites/' . $this->website . '/commands', [
            'command'   =>  'php artisan modules:enable NsPrintAdapter && php artisan modules:enable NsGastro'
        ]);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . ns()->option->get( 'nsdemo_forge_api' ),
            'Accept' => 'application/json',
        ])->post( 'https://forge.laravel.com/api/v1/servers/' . $this->server . '/sites/' . $this->website . '/commands', [
            'command'   =>  'php artisan modules:enable NsMultiStore'
        ]);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . ns()->option->get( 'nsdemo_forge_api' ),
            'Accept' => 'application/json',
        ])->post( 'https://forge.laravel.com/api/v1/servers/' . $this->server . '/sites/' . $this->website . '/commands', [
            'command'   =>  'php artisan modules:enable NsDemoFrontEnd'
        ]);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . ns()->option->get( 'nsdemo_forge_api' ),
            'Accept' => 'application/json',
        ])->post( 'https://forge.laravel.com/api/v1/servers/' . $this->server . '/sites/' . $this->website . '/commands', [
            'command'   =>  collect([
                'php artisan modules:symlink NsPrintAdapter',
                'php artisan modules:symlink NsGastro',
                'php artisan modules:migrate NsPrintAdapter',
                'php artisan modules:migrate NsGastro',
            ])->join( ' && ' )
        ]);

        ThirdStepResetJob::dispatch( $this->server, $this->website )
            ->delay( now()->addSeconds( 15 ) );
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
                __( 'Something went wrong 😓 for %s, while on SecondStepJob. Here is the error : %s.' ),
                url('/'),
                $exception->getMessage()
            )
        ]);
    }
}