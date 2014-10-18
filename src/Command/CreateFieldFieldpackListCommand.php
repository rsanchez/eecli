<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class CreateFieldFieldpackListCommand extends AbstractCreateFieldCommand
{
    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a Fieldpack List field.';

    /**
     * {@inheritdoc}
     */
    protected function getFieldtype()
    {
        return 'fieldpack_list';
    }
}
