<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class CreateFieldGridCommand extends AbstractCreateFieldCommand
{
    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a Grid field.';

    /**
     * {@inheritdoc}
     */
    protected function getFieldtype()
    {
        return 'grid';
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
            'grid_min_rows' => $this->option('min_rows'),
            'grid_max_rows' => $this->option('max_rows'),
            'grid' => array(
                'cols' => array(),
            ),
        );
    }
}
