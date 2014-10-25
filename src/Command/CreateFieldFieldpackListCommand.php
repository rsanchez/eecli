<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class CreateFieldFieldpackListCommand extends AbstractCreateFieldCommand implements HasExamples
{
    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a Fieldpack List field.';

    /**
     * {@inheritdoc}
     */
    protected function getFieldtype()
    {
        return 'fieldpack_list';
    }

    public function getExamples()
    {
        return array(
            'Create a Fieldpack List field in field group 1' => '"Your Field Name" your_field_name 1',
        );
    }
}
