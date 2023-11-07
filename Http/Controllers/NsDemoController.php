<?php
namespace Modules\NsDemo\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\NsDemo\Services\BotService;
use Modules\NsDemo\Settings\DemoSettings;

class NsDemoController extends Controller
{
    /**
     * @var BotService
     */
    private $botService;
    
    public function __construct(
        BotService $botService
    ) {
        $this->botService   =   $botService;
    }

    public function registerTelegramWebHook()
    {
        return $this->botService->setWebhook();
    }

    public function telegramWebhook( Request $request )
    {
        return $this->botService->handleWebHook( $request->all() );
    }

    public function unsetTelegramWebHook( Request $request )
    {
        return $this->botService->unsetWebhook();
    }

    public function settings()
    {
        return DemoSettings::renderForm();
    }
}