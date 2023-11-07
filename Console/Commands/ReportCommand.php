<?php
namespace Modules\NsDemo\Console\Commands;

use Illuminate\Console\Command;

class ReportCommand extends Command
{
    protected $signature    =   'ns:demo {action} {--message=}';
    protected $description  =   'Provide summary report on telegram regarding reset activities.';

    public function __construct()
    {
        parent::__construct();
    }
}