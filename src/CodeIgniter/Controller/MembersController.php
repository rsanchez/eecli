<?php

namespace eecli\CodeIgniter\Controller;

use eecli\Application;
use eecli\CodeIgniter\BootableInterface;

require_once APPPATH.'core/EE_Controller.php';
require_once APPPATH.'controllers/cp/members.php';

class MembersController extends \Members implements BootableInterface
{
    public function boot(Application $app)
    {
        $app->bootCp();

        ee()->lang->loadfile('members');
        ee()->load->model('member_model');
    }
}
