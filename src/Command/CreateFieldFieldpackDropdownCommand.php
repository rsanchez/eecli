<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class CreateFieldFieldpackDropdownCommand extends AbstractCreateFieldFieldpackOptionsCommand
{
    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a Fieldpack Dropdown field.';

    /**
     * {@inheritdoc}
     */
    protected function getFieldtype()
    {
        return 'fieldpack_dropdown';
    }
}
