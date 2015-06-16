<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;
use eecli\Command\Contracts\HasOptionExamples;
use eecli\Command\Contracts\HasLongDescription;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ClearCeCacheCommand extends Command implements HasExamples, HasOptionExamples, HasLongDescription
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
            array(
                'refresh',
                null,
                InputOption::VALUE_NONE,
                'Whether to refresh cache after clearing',
            ),
            array(
                'refresh_time',
                null,
                InputOption::VALUE_REQUIRED,
                'Number of seconds to wait between refreshing and deleting items'
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
        $refresh = $this->option('refresh') ? true : false;
        $refresh_time = $this->option('refresh_time');


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
                $driverName = lang('ce_cache_driver_'.$driver->name());

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

            $time = $refresh_time ?: 1;

            $defaultArgs = array(array(), array(), $refresh, $time);

            foreach ($items as $item) {
                $args = $defaultArgs;

                $args[$which][] = $item;

                call_user_func_array(array($breaker, 'break_cache'), $args);

                $this->comment($name.' '.$item.' cleared.');

                if ($refresh) {
                    $this->comment($name.' '.$item.' will be refreshed');
                }
            }
        }

        $this->info('CE Cache cleared.');
    }

    /**
     * {@inheritdoc}
     */
    public function getExamples()
    {
        return array(
            'Clear all caches' => '',
            'Clear a specific item' => 'local/foo/item',
            'Clear specific items' => 'local/foo/item local/bar/item',
            'Clear specific tags' => '--tags foo bar',
            'Clear specific driver' => '--driver="file"',
            'Set cache to refresh after clear' => '--refresh',
            'Set the number of seconds to wait before refreshing and deleting items' => '--refresh-time="2"',
        );
    }

    public function getLongDescription()
    {
        return "Clears the CE Cache.\n\nBe sure to set your [`http_host`](Global Options) when using the `refresh` option, so `eecli` will know your site's URL.";
    }

    public function getOptionExamples()
    {
        return array(
            'driver' => 'file',
            'refresh-time' => '1',
        );
    }
}
