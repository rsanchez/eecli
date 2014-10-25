<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;
use Illuminate\Console\Command;

class ClearStashCacheCommand extends Command implements HasExamples
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'cache:clear:stash';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Clears the Stash database cache.';

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        ee()->db->truncate('stash');

        $this->info('Stash cache cleared.');
    }

    /**
     * {@inheritdoc}
     */
    public function getExamples()
    {
        return array(
            'Clear all caches' => '',
        );
    }
}
