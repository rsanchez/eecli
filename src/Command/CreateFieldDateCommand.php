<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class CreateFieldDateCommand extends AbstractCreateFieldCommand
{
    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a Date field.';

    /**
     * {@inheritdoc}
     */
    protected function getFieldtype()
    {
        return 'date';
    }
}
