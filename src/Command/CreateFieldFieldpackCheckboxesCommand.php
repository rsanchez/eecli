<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class CreateFieldFieldpackCheckboxesCommand extends AbstractCreateFieldFieldpackOptionsCommand
{
    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a Fieldpack Checkboxes field.';

    /**
     * {@inheritdoc}
     */
    protected function getFieldtype()
    {
        return 'fieldpack_checkboxes';
    }
}
