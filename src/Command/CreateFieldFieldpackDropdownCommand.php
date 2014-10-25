<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class CreateFieldFieldpackDropdownCommand extends AbstractCreateFieldFieldpackOptionsCommand implements HasExamples
{
    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a Fieldpack Dropdown field.';

    /**
     * {@inheritdoc}
     */
    protected function getFieldtype()
    {
        return 'fieldpack_dropdown';
    }

    public function getExamples()
    {
        return array(
            'Create a Fieldpack Dropdown field in field group 1' => '"Your Field Name" your_field_name 1',
            'Create a Fieldpack Dropdown field with multiple options' => '--option="foo : Foo" --option="bar : Bar" "Name" name 1',
        );
    }
}
