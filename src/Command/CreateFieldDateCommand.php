<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;

class CreateFieldDateCommand extends AbstractCreateFieldCommand implements HasExamples
{
    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a Date field.';

    /**
     * {@inheritdoc}
     */
    protected function getFieldtype()
    {
        return 'date';
    }

    public function getExamples()
    {
        return array(
            'Create a Date field in field group 1' => '"Your Field Name" your_field_name 1',
        );
    }
}
