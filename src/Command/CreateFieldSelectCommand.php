<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class CreateFieldSelectCommand extends AbstractCreateFieldNativeOptionsCommand
{
    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a Select Dropdown field.';

    /**
     * {@inheritdoc}
     */
    protected function getFieldtype()
    {
        return 'select';
    }
}
