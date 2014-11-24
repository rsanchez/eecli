<?php

namespace eecli\CodeIgniter\Controller;

use eecli\Application;
use eecli\CodeIgniter\BootableInterface;

require_once APPPATH.'controllers/cp/admin_content.php';

class AdminContentController extends \Admin_content implements BootableInterface
{
    public function boot(Application $app)
    {
        $app->bootCp();

        ee()->lang->loadfile('admin');
        ee()->lang->loadfile('admin_content');
    }
}
