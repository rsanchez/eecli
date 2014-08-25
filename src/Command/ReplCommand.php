<?php

namespace eecli\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Boris\Boris;

class ReplCommand extends Command
{
    protected function configure()
    {
        $this->setName('repl');
        $this->setDescription('Start a REPL.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
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