<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class CreateFieldRadioCommand extends AbstractCreateFieldNativeOptionsCommand
{
    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a Radio Buttons field.';

    /**
     * {@inheritdoc}
     */
    protected function getFieldtype()
    {
        return 'radio';
    }
}
