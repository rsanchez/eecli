<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class CreateFieldSelectCommand extends AbstractCreateFieldNativeOptionsCommand implements HasExamples
{
    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a Select Dropdown field.';

    /**
     * {@inheritdoc}
     */
    protected function getFieldtype()
    {
        return 'select';
    }

    public function getExamples()
    {
        return array(
            'Create a Select field in field group 1' => '"Your Field Name" your_field_name 1',
            'Create a Select field with multiple options' => '--option="Foo" --option="Bar" "Name" name 1',
        );
    }
}
