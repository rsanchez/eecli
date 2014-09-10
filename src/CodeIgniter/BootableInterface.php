<?php

namespace eecli\CodeIgniter;

use eecli\Application;

interface BootableInterface
{
    /**
     * Initialize a new controller instance
     * @param  Application $app
     * @return void
     */
    public function boot(Application $app);
}
