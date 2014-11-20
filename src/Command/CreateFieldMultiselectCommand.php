<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;

class CreateFieldMultiselectCommand extends AbstractCreateFieldNativeOptionsCommand implements HasExamples
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

    public function getExamples()
    {
        return array(
            'Create a Multiselect field in field group 1' => '"Your Field Name" your_field_name 1',
            'Create a Multiselect field with multiple options' => '--option="Foo" --option="Bar" "Name" name 1',
        );
    }
}
