<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class CreateFieldFieldpackSwitchCommand extends AbstractCreateFieldCommand
{
    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a Fieldpack Switch field.';

    /**
     * {@inheritdoc}
     */
    protected function getFieldtype()
    {
        return 'fieldpack_switch';
    }

    /**
     * {@inheritdoc}
     */
    protected function getFieldtypeOptions()
    {
        return array(
            array(
                'off_label',
                null,
                InputOption::VALUE_REQUIRED,
                'OFF Label',
                'NO',
            ),
            array(
                'off_value',
                null,
                InputOption::VALUE_REQUIRED,
                'OFF Value',
            ),
            array(
                'on_label',
                null,
                InputOption::VALUE_REQUIRED,
                'ON Label',
                'YES',
            ),
            array(
                'on_value',
                null,
                InputOption::VALUE_REQUIRED,
                'ON Value',
                '1',
            ),
            array(
                'default',
                null,
                InputOption::VALUE_REQUIRED,
                'off or on',
                'off',
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getFieldtypeSettings()
    {
        return array(
            'pt_switch' => array(
                'off_label' => $this->option('off_label'),
                'off_val' => $this->option('off_value'),
                'on_label' => $this->option('on_label'),
                'on_val' => $this->option('on_value'),
                'default' => $this->option('default'),
            ),
        );
    }
}
