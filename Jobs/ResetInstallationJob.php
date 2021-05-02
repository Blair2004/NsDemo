<?php 
namespace Modules\NsDemo\Jobs;

use App\Jobs\ComputeCustomerAccountJob;
use App\Jobs\ComputeDashboardExpensesJob;
use App\Jobs\ComputeDashboardMonthReportJob;
use App\Jobs\ComputeDayReportJob;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Arr;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Modules\NsDemo\Services\BotService;
use Throwable;

class ResetInstallationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $botService     =   app()->make( BotService::class );

        try {
            Artisan::call( 'ns:reset' );
            Artisan::call( 'db:seed --class=DefaultSeeder' );
            dump( 'Process : ' . exec( 'vendor/bin/phpunit tests/Feature/ResetUserStatsTest.php' ) );
            dump( 'Process : ' . exec( 'vendor/bin/phpunit tests/Feature/CreateRegisterTest.php' ) );
            dump( 'Process : ' . exec( 'vendor/bin/phpunit tests/Feature/CreateTaxGroupTest.php' ) );
            dump( 'Process : ' . exec( 'vendor/bin/phpunit tests/Feature/CreateTaxTest.php' ) );
            Artisan::call( 'ns:bulkimport /storage/app/products.csv --email=contact@nexopos.com --config=/storage/app/import-config.json' );
            dump( 'Process : ' . exec( 'vendor/bin/phpunit tests/Feature/CreateOrderTest.php' ) );
            dump( 'Process : ' . exec( 'vendor/bin/phpunit tests/Feature/RefreshReportForPassDaysTest.php' ) );
            Artisan::call( 'storage:link' );

            ComputeDayReportJob::dispatchSync();
            ComputeDashboardMonthReportJob::dispatchSync();
    
            $botService->sendMessage([
                'chat_id'   =>  env( 'NS_BULKIMPORT_TELEGRAM_GROUP' ),
                'text'      =>  sprintf(
                    Arr::random([
                        __( 'The demo was successfully reset for %s 👍.' ),
                        __( 'Alright, i did reset the demo %s 👍' ),
                        __( 'Every went as expected on %s 👍' ),
                        __( 'Short message to let you know the demo as set for %s 👍' ),
                        __( 'The demo has been correctly reset for %s 👍' ),
                        __( 'The tasks is over and the demo was reset for %s 👍' ),
                        __( 'Until next request, i have reset the demo for %s 👍' ),
                        __( 'That was a pretty easy job for %s 👍. Demo is reset' ),
                        __( 'Confirmation message : demo is reset for %s 👍' ),
                        __( 'Hi, just to let you know the demo is reset for %s 👍' ),
                    ]),
                    url('/'),
                )
            ]);
        } catch( Exception $exception ) {
            $botService->sendMessage([
                'chat_id'   =>  env( 'NS_BULKIMPORT_TELEGRAM_GROUP' ),
                'text'      =>  sprintf(
                    __( 'Something went wrong 😓 for %s. Here is the error : %s.' ),
                    url('/'),
                    $exception->getMessage()
                )
            ]);
        }
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
                __( 'Something went wrong 😓 for %s. Here is the error : %s.' ),
                url('/'),
                $exception->getMessage()
            )
        ]);
    }
}