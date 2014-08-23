<?php

namespace eecli\eecli\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearEECacheCommand extends Command
{
    protected function configure()
    {
        $this->setName('cache:clear:ee');
        $this->setDescription('Clears the EE cache.');

        $this->addArgument(
            'type',
            InputArgument::OPTIONAL,
            'Which type do you want to clear? page, tag, db or all? (Leave blank to clear all)'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getArgument('type') ?: 'all';

        if (! in_array($type, ['page', 'tag', 'db', 'all'])) {
            $output->writeln('<error>Invalid cache type</error>');

            return;
        }

        ee()->functions->clear_caching($type);

        $suffix = $type === 'all' ? '' : ' '.$type;

        $output->writeln('<info>EE'.$suffix.' cache cleared.</info>');
    }
}