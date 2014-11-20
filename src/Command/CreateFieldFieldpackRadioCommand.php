<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;

class CreateFieldFieldpackRadioCommand extends AbstractCreateFieldFieldpackOptionsCommand implements HasExamples
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

    public function getExamples()
    {
        return array(
            'Create a Fieldpack Radio Buttons field in field group 1' => '"Your Field Name" your_field_name 1',
            'Create a Fieldpack Radio Buttons field with multiple options' => '--option="foo : Foo" --option="bar : Bar" "Name" name 1',
        );
    }
}
