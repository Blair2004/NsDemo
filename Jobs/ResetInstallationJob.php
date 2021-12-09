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
            Artisan::call( 'ns:reset --mode=hard' );
            Artisan::call( 'ns:setup --admin_username=admin --admin_email=contact@nexopos.com --password=123456 --store_name="NexoPOS 4x"' );
            Artisan::call( 'env:set NS_MODULES_MANAGEMENT_DISABLED --v=false' );
            Artisan::call( 'modules:enable NsDemo' );
            Artisan::call( 'modules:enable NsBulkImporter' );
            Artisan::call( 'env:set NS_MODULES_MANAGEMENT_DISABLED --v=true' );
            Artisan::call( 'storage:link' );
            Artisan::call( 'ns:reset --mode=grocery' );
            dump( 'Process : ' . exec( 'vendor/bin/phpunit tests/Feature/CreateOrderTest.php' ) );

            ComputeDayReportJob::dispatchSync();
            ComputeDashboardMonthReportJob::dispatchSync();
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

    public function deprecatedhandle()
    {
        $botService     =   app()->make( BotService::class );

        try {
            Artisan::call( 'ns:reset --mode=hard' );
            Artisan::call( 'ns:setup --admin_username=admin --admin_email=contact@nexopos.com --password=123456 --store_name="NexoPOS 4x"' );
            Artisan::call( 'env:set NS_MODULES_MANAGEMENT_DISABLED --v=false' );
            Artisan::call( 'modules:enable NsDemo' );
            Artisan::call( 'modules:enable NsBulkImporter' );
            Artisan::call( 'env:set NS_MODULES_MANAGEMENT_DISABLED --v=true' );
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