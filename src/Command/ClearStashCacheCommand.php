<?php

namespace eecli\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearStashCacheCommand extends Command
{
    protected function configure()
    {
        $this->setName('cache:clear:stash');
        $this->setDescription('Clears the Stash database cache.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ee()->db->truncate('stash');

        $output->writeln('<info>Stash cache cleared.</info>');
    }
}