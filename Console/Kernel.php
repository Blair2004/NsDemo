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
        //
    }
}