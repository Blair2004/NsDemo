<?php
namespace Modules\NsDemo\Console\Commands;

use Illuminate\Console\Command;
use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Request;

class ReportCommand extends Command
{
    protected $signature    =   'ns:demo {action}';
    protected $description  =   'Provide summary report on telegram regarding reset activities.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
    }
}