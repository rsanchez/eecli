<?php

namespace eecli\CodeIgniter\Controller;

use eecli\Application;
use eecli\CodeIgniter\BootableInterface;

require_once APPPATH.'controllers/cp/design.php';

class DesignController extends \Design implements BootableInterface
{
    public function boot(Application $app)
    {
        $app->bootCp();

        ee()->lang->loadfile('design');
    }
}
