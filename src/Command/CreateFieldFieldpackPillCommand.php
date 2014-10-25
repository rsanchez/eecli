<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class CreateFieldFieldpackPillCommand extends AbstractCreateFieldFieldpackOptionsCommand implements HasExamples
{
    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a Fieldpack Pill field.';

    /**
     * {@inheritdoc}
     */
    protected function getFieldtype()
    {
        return 'fieldpack_pill';
    }

    protected function getOptionPrefix()
    {
        return 'pt_pill';
    }

    public function getExamples()
    {
        return array(
            'Create a Fieldpack Pill field in field group 1' => '"Your Field Name" your_field_name 1',
            'Create a Fieldpack Pill field with multiple options' => '--option="foo : Foo" --option="bar : Bar" "Name" name 1',
        );
    }
}
