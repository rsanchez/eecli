<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;

class CreateFieldFieldpackCheckboxesCommand extends AbstractCreateFieldFieldpackOptionsCommand implements HasExamples
{
    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a Fieldpack Checkboxes field.';

    /**
     * {@inheritdoc}
     */
    protected function getFieldtype()
    {
        return 'fieldpack_checkboxes';
    }

    public function getExamples()
    {
        return array(
            'Create a Fieldpack Checkboxes field in field group 1' => '"Your Field Name" your_field_name 1',
            'Create a Fieldpack Checkboxes field with multiple options' => '--option="foo : Foo" --option="bar : Bar" "Name" name 1',
        );
    }
}
