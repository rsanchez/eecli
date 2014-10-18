<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class CreateFieldFieldpackPillCommand extends AbstractCreateFieldFieldpackOptionsCommand
{
    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a Fieldpack Pill field.';

    /**
     * {@inheritdoc}
     */
    protected function getFieldtype()
    {
        return 'fieldpack_pill';
    }

    protected function getOptionPrefix()
    {
        return 'pt_pill';
    }
}
