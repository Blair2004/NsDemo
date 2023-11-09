<?php
namespace Modules\NsDemo\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\NsDemo\Crud\DemoInstancesCrud;
use Modules\NsDemo\Jobs\FirstStepResetJob;
use Modules\NsDemo\Models\DemoInstance;
use Modules\NsDemo\Services\BotService;
use Modules\NsDemo\Services\ForgeService;
use Modules\NsDemo\Settings\DemoSettings;

class NsDemoController extends Controller
{
    public function __construct(
        protected BotService $botService,
        protected ForgeService $forgeService
    ) {
        // ...
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

    public function getInstances()
    {
        return DemoInstancesCrud::table();
    }

    public function editInstances( DemoInstance $instance )
    {
        return DemoInstancesCrud::form( $instance );
    }

    public function createInstances()
    {
        return DemoInstancesCrud::form();
    }

    public function triggerInstances( DemoInstance $instance )
    {
        $this->forgeService->triggerInstances( $instance );

        return [
            'status'    =>  'success',
            'message'   =>  __( 'The demo was successfully triggered' )
        ];
    }
}