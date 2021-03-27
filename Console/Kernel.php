<?php
namespace Modules\NsDemo\Console;

use App\Console\Kernel as ConsoleKernel;
use Illuminate\Console\Scheduling\Schedule;

class Kernel extends ConsoleKernel
{
    protected function schedule( Schedule $schedule )
    {
        $schedule->call( function() {
            
        })->cron( '0 */6 * * *' );

        $schedule->call( function() {
            $Telegram   =   new Telegram( 
                env( 'NS_BULKIMPORT_TELEGRAM_TOKEN' ),
                env( 'NS_BULKIMPORT_TELEGRAM_USERNAME' ),
            );
        })->everyMinute();

        dd( 'foo' );
    }
}