<?php
namespace Modules\NsDemo\Jobs;

use App\Jobs\ComputeDashboardMonthReportJob;
use App\Jobs\ComputeDayReportJob;
use App\Models\Role;
use App\Models\User;
use App\Services\DemoService;
use App\Services\ModulesService;
use App\Services\ResetService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Modules\NsDemo\Console\Commands\ReportCommand;
use Modules\NsGastro\Services\RestaurantDemoService;
use Modules\NsMultiStore\Models\Store;
use Throwable;

class SecondStepJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function handle( ModulesService $modules )
    {
        /**
         * let's auth the administrator
         * he should exist from this moment
         */
        $admin  =   Role::namespace( Role::ADMIN )->users->firstOrFail();
        Auth::loginUsingId( $admin->id );

        if ( $modules->getIfEnabled( 'NsGastro' ) && ! $modules->getIfEnabled( 'NsMultiStore' ) ) {
            $this->resetRestaurantDemo();
        } else if ( $modules->getIfEnabled( 'NsMultiStore' ) && ! $modules->getIfEnabled( 'NsGastro' ) ) {   
            /**
             * @var User
             */
            $user   =   Role::namespace( 'admin' )->users()->first();

            Artisan::call( 'multistore:wipe --force' );
            Artisan::call( 'multistore:create "Grocery Master" --user ' . $user->email . '--roles admin' );

            $lastStore  =   Store::orderBy( 'id', 'desc' )->first();
            ns()->store->setStore( $lastStore );
            $this->resetGroceryDemo();

        } else if ( $modules->getIfEnabled( 'NsMultiStore' ) && $modules->getIfEnabled( 'NsGastro' ) ) {   
            /**
             * @var User
             */
            $user   =   Role::namespace( 'admin' )->users()->first();

            Artisan::call( 'multistore:wipe --force' );
            Artisan::call( 'multistore:create "Chef Master - Restaurant" --user ' . $user->email . '--roles admin --roles ' . Role::STORECASHIER );

            $lastStore  =   Store::orderBy( 'id', 'desc' )->first();
            ns()->store->setStore( $lastStore );
            $this->resetGroceryDemo();

        } else {
            Artisan::call( 'ns:reset --mode=grocery' );
        }

        ComputeDayReportJob::dispatchSync();
        ComputeDashboardMonthReportJob::dispatchSync();

        Artisan::call( 'optimize:clear' );
        Artisan::call( 'ns:demo notify --message="Demo successfully reinitialized for %s"' );
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
                __( 'Something went wrong 😓 for %s, while on SecondStepJob. Here is the error : %s.' ),
                url('/'),
                $exception->getMessage()
            )
        ]);
    }

    public function resetRestaurantDemo()
    {
        /**
         * @var ResetService
         */
        $resetService = app()->make(ResetService::class);
        $resetService->softReset();

        /**
         * @var RestaurantDemoService
         */
        $demoService = app()->make(RestaurantDemoService::class);
        $demoService->run([
            'create_sales'          =>  true,
            'create_procurements'   =>  true,
        ]);
    }

    public function resetGroceryDemo()
    {
        /**
         * @var ResetService
         */
        $resetService = app()->make(ResetService::class);
        $resetService->softReset();

        /**
         * @var RestaurantDemoService
         */
        $demoService = app()->make(DemoService::class);
        $demoService->run([
            'create_sales'          =>  true,
            'create_procurements'   =>  true,
        ]);
    }
}