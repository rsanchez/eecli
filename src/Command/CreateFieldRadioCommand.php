<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class CreateFieldRadioCommand extends AbstractCreateFieldNativeOptionsCommand implements HasExamples
{
    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a Radio Buttons field.';

    /**
     * {@inheritdoc}
     */
    protected function getFieldtype()
    {
        return 'radio';
    }

    public function getExamples()
    {
        return array(
            'Create a Radio field in field group 1' => '"Your Field Name" your_field_name 1',
            'Create a Radio field with multiple options' => '--option="Foo" --option="Bar" "Name" name 1',
        );
    }
}
