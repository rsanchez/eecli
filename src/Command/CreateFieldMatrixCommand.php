<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;
use Symfony\Component\Console\Input\InputOption;

class CreateFieldMatrixCommand extends AbstractCreateFieldCommand implements HasExamples
{
    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a Matrix field.';

    /**
     * {@inheritdoc}
     */
    protected function getFieldtype()
    {
        return 'matrix';
    }

    /**
     * {@inheritdoc}
     */
    protected function getFieldtypeOptions()
    {
        return array(
            array(
                'min_rows',
                null,
                InputOption::VALUE_REQUIRED,
                'What is the minimum number of rows?',
                '0',
            ),
            array(
                'max_rows',
                null,
                InputOption::VALUE_REQUIRED,
                'What is the maximum number of rows?',
                '',
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getFieldtypeSettings()
    {
        return array(
            'matrix' => array(
                'min_rows' => $this->option('min_rows'),
                'max_rows' => $this->option('max_rows'),
                'col_order' => array(),
                'cols' => array(),
            ),
        );
    }

    protected function getFieldtypeOptionExamples()
    {
        return array(
            'max_rows' => '3',
        );
    }

    public function getExamples()
    {
        return array(
            'Create a Matrix field in field group 1' => '"Your Field Name" your_field_name 1',
            'Create a Matrix field with max and min rows' => '--min_rows="1" --max_rows="3" "Name" name 1',
        );
    }
}
