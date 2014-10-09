<?php

namespace eecli\Command\Contracts;

use eecli\Application;
use Symfony\Component\Console\Input\InputInterface;

interface HasRuntimeOptions
{
    /**
     * Get an array of Symfony\Component\Console\Input\InputOption objects
     * @param  Application    $app
     * @param  InputInterface $input
     * @return array
     */
    public function getRuntimeOptions(Application $app, InputInterface $input);
}
