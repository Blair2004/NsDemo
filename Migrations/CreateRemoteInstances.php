<?php
/**
 * Table Migration
 * @package 4.8.22
**/

namespace Modules\NsDemo\Migrations;

use App\Classes\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRemoteInstances extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasTable( 'ns_demo_instances' ) ) {
            Schema::create( 'ns_demo_instances', function( Blueprint $table ) {
                $table->bigIncrements( 'id' );
                $table->string( 'name' );
                $table->string( 'forge_id' );
                $table->text( 'commands' );
                $table->text( 'description' )->nullable();
                $table->integer( 'author' );
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists( 'ns_demo_instances' );
    }
}
