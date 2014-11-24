<?php

namespace eecli\Command;

class CreateFieldCheckboxesCommand extends AbstractCreateFieldNativeOptionsCommand
{
    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a Checkboxes field.';

    /**
     * {@inheritdoc}
     */
    protected function getFieldtype()
    {
        return 'checkboxes';
    }

    public function getExamples()
    {
        return array(
            'Create a Checkboxes field in field group 1' => '"Your Field Name" your_field_name 1',
            'Create a Checkboxes field with multiple options' => '--option="Foo" --option="Bar" "Name" name 1',
        );
    }
}
