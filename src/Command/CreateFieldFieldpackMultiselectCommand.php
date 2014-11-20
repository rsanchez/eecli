<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;

class CreateFieldFieldpackMultiselectCommand extends AbstractCreateFieldFieldpackOptionsCommand implements HasExamples
{
    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a Fieldpack Multiselect field.';

    /**
     * {@inheritdoc}
     */
    protected function getFieldtype()
    {
        return 'fieldpack_multiselect';
    }

    public function getExamples()
    {
        return array(
            'Create a Fieldpack Multiselect field in field group 1' => '"Your Field Name" your_field_name 1',
            'Create a Fieldpack Multiselect field with multiple options' => '--option="foo : Foo" --option="bar : Bar" "Name" name 1',
        );
    }
}
