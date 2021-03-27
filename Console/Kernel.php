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
    }
}