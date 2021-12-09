<?php
namespace Modules\NsDemo\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Request;
use Modules\NsDemo\Services\BotService;

class ReportCommand extends Command
{
    protected $signature    =   'ns:demo {action} {--message=}';
    protected $description  =   'Provide summary report on telegram regarding reset activities.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        switch( $this->argument( 'action' ) ) {
            case 'notify':
                $this->notifyAdministrators();
            break;
        }
    }

    public function notifyAdministrators()
    {
        $botService     =   app()->make( BotService::class );
        $botService->sendMessage([
            'chat_id'   =>  env( 'NS_BULKIMPORT_TELEGRAM_GROUP' ),
            'text'      =>  sprintf(
                $this->option( 'message' ) ?? Arr::random([
                    __( 'The demo was successfully reset for %s 👍.' ),
                    __( 'Alright, i did reset the demo %s 👍' ),
                    __( 'Every went as expected on %s 👍' ),
                    __( 'Short message to let you know the demo as set for %s 👍' ),
                    __( 'The demo has been correctly reset for %s 👍' ),
                    __( 'The tasks is over and the demo was reset for %s 👍' ),
                    __( 'Until next request, i have reset the demo for %s 👍' ),
                    __( 'That was a pretty easy job for %s 👍. Demo is reset' ),
                    __( 'Confirmation message : demo is reset for %s 👍' ),
                    __( 'Hi, just to let you know the demo is reset for %s 👍' ),
                ]),
                url('/'),
            )
        ]);
    }
}