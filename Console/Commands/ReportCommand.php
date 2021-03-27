<?php
namespace Modules\NsDemo\Console\Commands;

use Illuminate\Console\Command;
use Longman\TelegramBot\Telegram;

class ReportCommand extends Command
{
    protected $signature    =   'ns:nsdemo {action}';
    protected $description  =   'Provide summary report on telegram regarding reset activities.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $Telegram   =   new Telegram( 
            env( 'NS_BULKIMPORT_TELEGRAM_TOKEN' ),
            env( 'NS_BULKIMPORT_TELEGRAM_USERNAME' ),
        );
    }
}