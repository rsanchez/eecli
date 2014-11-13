<?php

namespace eecli\CodeIgniter\Controller;

use eecli\Application;
use eecli\CodeIgniter\BootableInterface;

require_once APPPATH.'core/EE_Controller.php';
require_once APPPATH.'controllers/cp/content_files.php';

class ContentFilesController extends \Content_files implements BootableInterface
{
    public function boot(Application $app)
    {
        $app->bootCp();
    }
}
