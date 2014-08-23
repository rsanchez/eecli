<?php

namespace eecli\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearCeCacheCommand extends Command
{
    protected function configure()
    {
        $this->setName('cache:clear:ce_cache');
        $this->setDescription('Clears the CE Cache.');

        $this->addOption(
            'tags',
            null,
            InputOption::VALUE_NONE,
            'Whether to delete by tag.'
        );

        $this->addArgument(
            'items',
            InputArgument::IS_ARRAY,
            'Which items do you wish to clear? (Leave blank to clear all)'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ee()->load->library('session');
        ee()->lang->loadfile('ce_cache', 'ce_cache');

        $items = $input->getArgument('items');
        $tags = $input->getOption('tags');

        // if there are no arguments, clear all caches
        if (!$items) {

            require_once PATH_THIRD.'ce_cache/libraries/Ce_cache_factory.php';

            $drivers = \Ce_cache_factory::factory(['file', 'db', 'static', 'apc', 'memcache', 'memcached', 'redis', 'dummy']);

            foreach ($drivers as $driver) {
                $driverName = lang('ce_cache_driver_'. $driver->name());

                if ($driver->clear() === false) {
                    $output->writeln('<error>'.sprintf(lang('ce_cache_error_cleaning_driver_cache'), $driverName).'</comment>');
                } else {
                    $output->writeln('<comment>'.$driverName.' cache cleared.</comment>');
                }
            }

        } else {

            require_once PATH_THIRD.'ce_cache/libraries/Ce_cache_break.php';

            $breaker = new \Ce_cache_break();

            $name = $tags ? 'Tag' : 'Item';

            $which = $tags ? 1 : 0;

            $defaultArgs = [[], [], false];

            foreach ($items as $item) {

                $args = $defaultArgs;

                $args[$which][] = $item;

                call_user_func_array(array($breaker, 'break_cache'), $args);

                $output->writeln('<comment>'.$name.' '.$item.' cleared.</comment>');
            }
        }

        $output->writeln('<info>CE Cache cleared.</info>');
    }
}