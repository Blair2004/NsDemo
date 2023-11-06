<?php

namespace Modules\NsDemo\Jobs;

use App\Services\ModulesService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Modules\NsDemo\Services\BotService;
use Throwable;

class FirstStepResetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle( ModulesService $modules )
    {
        /**
         * get all the modules that are
         * enabled.
         */
        $enabledModules = ns()->option->get( 'enabled_modules', [] );

        /**
         * We'll enable the module and make sure it's stored
         * on the option table only once.
         */
        if ( ! in_array( 'NsDemo', $enabledModules ) ) {
            $enabledModules[] = 'NsDemo';            
        }

        if ( ! in_array( 'NsGastro', $enabledModules ) && $modules->get( 'NsGastro' ) ) {
            // $enabledModules[] = 'NsGastro';            
        }

        if ( ! in_array( 'NsMultiStore', $enabledModules ) && $modules->get( 'NsMultiStore' ) ) {
            // $enabledModules[] = 'NsMultiStore';            
        }

        ns()->option->set( 'enabled_modules', $enabledModules );

        if ( $modules->getIfEnabled( 'NsGastro' ) ) {
            Artisan::call( 'modules:migrate NsGastro' );
        }

        if ( $modules->getIfEnabled( 'NsMultiStore' ) ) {
            Artisan::call( 'modules:migrate NsMultiStore' );
        }
        
        Artisan::call( 'optimize:clear' );
        SecondStepJob::dispatch();
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed( Throwable $exception)
    {
        $botService     =   app()->make( BotService::class );
        $botService->sendMessage([
            'chat_id'   =>  env( 'NS_BULKIMPORT_TELEGRAM_GROUP' ),
            'text'      =>  sprintf(
                __( 'Something went wrong 😓 for %s, while on FirstStepResetJob. Here is the error : %s.' ),
                url('/'),
                $exception->getMessage()
            )
        ]);
    }
}