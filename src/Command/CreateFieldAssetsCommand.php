<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class CreateFieldAssetsCommand extends AbstractCreateFieldCommand implements HasExamples
{
    /**
     * {@inheritdoc}
     */
    protected $description = 'Create an Assets field.';

    /**
     * {@inheritdoc}
     */
    protected function getFieldtype()
    {
        return 'assets';
    }

    /**
     * {@inheritdoc}
     */
    protected function getFieldtypeOptions()
    {
        return array(
            array(
                'upload_dir',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'ID of upload dir (Leave blank for all)',
            ),
            array(
                'view',
                null,
                InputOption::VALUE_REQUIRED,
                'thumbs or list',
                'thumbs',
            ),
            array(
                'thumb_size',
                null,
                InputOption::VALUE_REQUIRED,
                'small or large',
                'small',
            ),
            array(
                'show_filenames',
                null,
                InputOption::VALUE_NONE,
                'Show filenames?',
            ),
            array(
                'multiple',
                null,
                InputOption::VALUE_NONE,
                'Allow multiple selections',
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getFieldtypeSettings()
    {
        $dirs = $this->option('upload_dir');

        // validate these as ee:1, etc
        if ($dirs) {
            foreach ($dirs as $dir) {
                if (! preg_match('/^(ee|s3|rs|gc):(\d+)$/', $dir)) {
                    throw new \RuntimeException('Upload dir is not in the proper format. ex. ee:1, sc:2');
                }
            }
        }

        return array(
            'assets' => array(
                'filedirs' => $dirs ?: array('all'),
                'view' => $this->option('view'),
                'thumb_size' => $this->option('thumb_size'),
                'show_cols' => array('folder', 'date', 'size'),
                'show_filenames' => $this->option('show_filenames') ? 'y' : 'n',
                'multi' => $this->option('multiple') ? 'y' : 'n',
            ),
        );
    }

    public function getExamples()
    {
        return array(
            'Create an Assets field in field group 1' => '"Your Field Name" your_field_name 1',
            'Create an Assets field that uploads to EE directory 1' => '--upload_dir="ee:1" "Your Field Name" your_field_name 1',
            'Create an Assets field that uploads to S3 directory 2' => '--upload_dir="s3:2" "Your Field Name" your_field_name 1',
            'Create an Assets field that allows multiple selections' => '--multiple "Your Field Name" your_field_name 1',
        );
    }

    protected function getFieldtypeOptionExamples()
    {
        return array(
            'upload_dir' => '1',
        );
    }
}
