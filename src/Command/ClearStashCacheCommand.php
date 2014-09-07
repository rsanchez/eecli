<?php

namespace eecli\Command;

use Illuminate\Console\Command;

class ClearStashCacheCommand extends Command
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
}
