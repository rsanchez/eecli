<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class CreateFieldRteCommand extends AbstractCreateFieldCommand
{
    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a Textarea (Rich) field.';

    /**
     * {@inheritdoc}
     */
    protected function getFieldtype()
    {
        return 'rte';
    }

    /**
     * {@inheritdoc}
     */
    protected function getFieldtypeOptions()
    {
        return array(
            array(
                'rows',
                null,
                InputOption::VALUE_REQUIRED,
                'The number of textarea rows',
                10,
            ),
            array(
                'text_direction',
                null,
                InputOption::VALUE_REQUIRED,
                'ltr or rtl',
                'ltr',
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getFieldtypeSettings()
    {
        return array(
            'rte_ta_rows' => $this->option('rows'),
            'rte_field_text_direction' => $this->option('text_direction'),
        );
    }
}
