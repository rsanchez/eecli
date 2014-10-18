<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class CreateFieldMultiselectCommand extends AbstractCreateFieldNativeOptionsCommand
{
    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a Multiselect field.';

    /**
     * {@inheritdoc}
     */
    protected function getFieldtype()
    {
        return 'multi_select';
    }
}
