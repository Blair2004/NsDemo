<?php
namespace Modules\NsDemo;

use App\Services\Module;

class DemoModule extends Module
{
    public function __construct()
    {
        parent::__construct( __FILE__ );
    }
}