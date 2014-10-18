<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class CreateFieldFieldpackRadioCommand extends AbstractCreateFieldFieldpackOptionsCommand
{
    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a Fieldpack Radio Buttons field.';

    /**
     * {@inheritdoc}
     */
    protected function getFieldtype()
    {
        return 'fieldpack_radio_buttons';
    }
}
