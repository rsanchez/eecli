<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ClearCeCacheCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'cache:clear:ce_cache';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Clears the CE Cache.';

    /**
     * {@inheritdoc}
     */
    protected function getOptions()
    {
        return array(
            array(
                'tags',
                null,
                InputOption::VALUE_NONE,
                'Whether to delete by tag.',
            ),
            array(
                'driver',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Which driver to clear',
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getArguments()
    {
        return array(
            array(
                'items',
                InputArgument::IS_ARRAY,
                'Which items do you wish to clear? (Leave blank to clear all)',
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        ee()->lang->loadfile('ce_cache', 'ce_cache');

        $items = $this->argument('items');
        $tags = $this->option('tags');
        $drivers = $this->option('driver');

        $defaultDrivers = array('file', 'db', 'static', 'apc', 'memcache', 'memcached', 'redis', 'dummy');

        if ($drivers) {
            $invalidDrivers = array_diff($drivers, $defaultDrivers);

            if ($invalidDrivers) {
                throw new \RuntimeException('Invalid driver(s) specified: '.implode(', ', $invalidDrivers));
            }

            $drivers = array_intersect($drivers, $defaultDrivers);
        } else {
            $drivers = $defaultDrivers;
        }

        // if there are no arguments, clear all caches
        if (! $items) {

            require_once PATH_THIRD.'ce_cache/libraries/Ce_cache_factory.php';

            $drivers = \Ce_cache_factory::factory($drivers);

            foreach ($drivers as $driver) {
                $driverName = lang('ce_cache_driver_'. $driver->name());

                if ($driver->clear() === false) {
                    $this->error(sprintf(lang('ce_cache_error_cleaning_driver_cache'), $driverName));
                } else {
                    $this->comment($driverName.' cache cleared.');
                }
            }

        } else {

            require_once PATH_THIRD.'ce_cache/libraries/Ce_cache_break.php';

            $breaker = new \Ce_cache_break();

            $name = $tags ? 'Tag' : 'Item';

            $which = $tags ? 1 : 0;

            $defaultArgs = array(array(), array(), false);

            foreach ($items as $item) {

                $args = $defaultArgs;

                $args[$which][] = $item;

                call_user_func_array(array($breaker, 'break_cache'), $args);

                $this->comment($name.' '.$item.' cleared.');
            }
        }

        $this->info('CE Cache cleared.');
    }
}
