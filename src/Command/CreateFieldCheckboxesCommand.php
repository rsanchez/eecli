<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class CreateFieldCheckboxesCommand extends AbstractCreateFieldNativeOptionsCommand
{
    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a Checkboxes field.';

    /**
     * {@inheritdoc}
     */
    protected function getFieldtype()
    {
        return 'checkboxes';
    }
}
