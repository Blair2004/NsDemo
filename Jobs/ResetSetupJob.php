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
use Modules\NsDemo\Services\ForgeService;
use Throwable;

class ResetSetupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle( ForgeService $forgeService )
    {
        $forgeService->resetSelectedWebsites();
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