<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class ClearEECacheCommand extends AbstractCommand implements HasExamples
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'cache:clear:ee';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Clears the EE cache.';

    /**
     * {@inheritdoc}
     */
    protected function getArguments()
    {
        return array(
            array(
                'type',
                InputArgument::OPTIONAL,
                'Which type do you want to clear? page, tag, db or all? (Leave blank to clear all)',
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        $validTypes = array('page', 'tag', 'db', 'all');

        $type = $this->argument('type') ?: 'all';

        if (! in_array($type, $validTypes)) {
            $this->error('Invalid cache type');

            return;
        }

        ee()->functions->clear_caching($type);

        $suffix = $type === 'all' ? '' : ' '.$type;

        $this->info('EE'.$suffix.' cache cleared.');
    }

    /**
     * {@inheritdoc}
     */
    public function getExamples()
    {
        return array(
            'Clear all EE caches' => '',
            'Clear EE page caches' => 'page',
            'Clear EE db caches' => 'db',
            'Clear EE tag caches' => 'tag',
        );
    }
}
