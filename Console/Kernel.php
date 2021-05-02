<?php
namespace Modules\NsDemo\Console;

use App\Console\Kernel as ConsoleKernel;
use Illuminate\Console\Scheduling\Schedule;
use Modules\NsDemo\Jobs\ResetInstallationJob;
use Modules\NsDemo\Services\BotService;

class Kernel extends ConsoleKernel
{
    protected function schedule( Schedule $schedule )
    {
        $schedule->call( function() {
            $botService     =   app()->make( BotService::class );
            $botService->sendMessage([
                'chat_id'   =>  env( 'NS_BULKIMPORT_TELEGRAM_GROUP' ),
                'text'      =>  sprintf(
                    __( 'I\'ll reset the demo for the installation %s shortly.' ),
                    url('/')
                )
            ]);

            ResetInstallationJob::dispatchSync();

        })->cron( '0 1 * * *' );
    }
}