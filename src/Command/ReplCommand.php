<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Boris\Boris;

class ReplCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'repl';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Start an interactive shell.';

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        $requiredExtensions = array('readline', 'posix', 'pcntl');

        foreach ($requiredExtensions as $extension) {
            if (! extension_loaded($extension)) {
                throw new \Exception(sprintf('PHP %s extension is required for this command.', $extension));
            }
        }

        $boris = new Boris('> ');

        $boris->start();
    }
}
