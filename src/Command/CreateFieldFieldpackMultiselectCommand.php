<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class CreateFieldFieldpackMultiselectCommand extends AbstractCreateFieldFieldpackOptionsCommand
{
    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a Fieldpack Multiselect field.';

    /**
     * {@inheritdoc}
     */
    protected function getFieldtype()
    {
        return 'fieldpack_multiselect';
    }
}
