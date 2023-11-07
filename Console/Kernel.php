<?php
namespace Modules\NsDemo\Console;

use App\Console\Kernel as ConsoleKernel;
use Illuminate\Console\Scheduling\Schedule;
use Modules\NsDemo\Jobs\ResetSetupJob;

class Kernel extends ConsoleKernel
{
    protected function schedule( Schedule $schedule )
    {
        $schedule->job( ResetSetupJob::class )->everyThreeMinutes();
    }
}