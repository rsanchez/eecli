<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class CreateFieldMatrixCommand extends AbstractCreateFieldCommand
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
                0,
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
}
