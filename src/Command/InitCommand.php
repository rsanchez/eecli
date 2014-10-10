<?php

namespace eecli\Command;

use eecli\Command\Contracts\ExemptFromBootstrap;
use Illuminate\Console\Command;

class InitCommand extends Command implements ExemptFromBootstrap
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'init';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Creates a default configuration file.';

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        $cwd = getcwd();

        $destination = $cwd.'/.eecli.php';

        if (file_exists($destination)) {
            $confirmed = $this->confirm('A configuration file already exists. Do you want to overwrite? y[n]', false);

            if (! $confirmed) {
                return;
            }
        }

        $source = file_get_contents(__DIR__.'/../../sample.eecli.php');

        if ($source === false) {
            $this->error('Could not read the sample.eecli.php file.');

            return;
        }

        $systemPath = $this->getApplication()->findSystemPath();

        if ($systemPath) {

            $cwdLength = strlen($cwd);

            //check if the system path is in the cwd
            if (strncmp($cwd, $systemPath, $cwdLength) === 0) {
                $systemPath = substr($systemPath, $cwdLength);

                $replacement = "__DIR__.'{$systemPath}'";
            } else {
                $replacement = "'{$systemPath}'";
            }

            $source = str_replace("__DIR__.'/system'", $replacement, $source);
        }

        $handle = fopen($destination, 'w');

        if ($handle === false) {
            $this->error('Could not open '.$destination.' for writing.');

            return;
        }

        $write = fwrite($handle, $source);

        if ($write === false) {
            $this->error('Could not write to '.$destination);

            return;
        }

        fclose($handle);

        $this->info('Configuration file created.');
    }
}
