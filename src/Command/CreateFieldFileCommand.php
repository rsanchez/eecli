<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class CreateFieldFileCommand extends AbstractCreateFieldCommand implements HasExamples
{
    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a File field.';

    /**
     * {@inheritdoc}
     */
    protected function getFieldtype()
    {
        return 'file';
    }

    /**
     * {@inheritdoc}
     */
    protected function getFieldtypeOptions()
    {
        return array(
            array(
                'content_type',
                null,
                InputOption::VALUE_REQUIRED,
                'all or image',
                'all',
            ),
            array(
                'upload_dir',
                null,
                InputOption::VALUE_REQUIRED,
                'ID of upload dir',
                'all',
            ),
            array(
                'hide_existing',
                null,
                InputOption::VALUE_NONE,
                'Hide existing files in a Channel Form?',
            ),
            array(
                'limit',
                null,
                InputOption::VALUE_REQUIRED,
                'How many existing files to show in a Channel Form?',
                50,
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getFieldtypeSettings()
    {
        return array(
            'file_field_content_type' => $this->option('content_type'),
            'file_allowed_directories' => $this->option('upload_dir'),
            'file_show_existing' => $this->option('hide_existing') ? 'n' : 'y',
            'file_num_existing' => $this->option('limit'),
        );
    }

    protected function getFieldtypeOptionExamples()
    {
        return array(
            'upload_dir' => '1',
        );
    }

    public function getExamples()
    {
        return array(
            'Create a File field in field group 1' => '"Your Field Name" your_field_name 1',
            'Create a File field that uploads to directory 1' => '--upload_dir=1 "Your Field Name" your_field_name 1',
            'Create a File field that only allows images' => '--content_type=image "Your Field Name" your_field_name 1',
        );
    }
}
