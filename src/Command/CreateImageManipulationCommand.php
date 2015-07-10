<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use eecli\Command\Contracts\HasExamples;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CreateImageManipulationCommand extends AbstractCommand implements HasExamples
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'create:image_manipulation';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a new image manipulation.';

    /**
     * {@inheritdoc}
     */
    protected function getArguments()
    {
        return array(
            array(
                'short_name', // name
                InputArgument::REQUIRED, // mode
                'Short Name', // description
            ),
            array(
                'upload_location', // name
                InputArgument::REQUIRED, // mode
                'Upload Location Id or Name', // description
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getOptions()
    {
        return array(
            array(
                'resize_type', // name
                null, // shortcut
                InputOption::VALUE_REQUIRED, // mode
                'Resize Type', // description
                'none'
            ),
            array(
                'width', // name
                null, // shortcut
                InputOption::VALUE_REQUIRED, // mode
                'Width', // description
            ),
            array(
                'height', // name
                null, // shortcut
                InputOption::VALUE_REQUIRED, // mode
                'Height', // description
            ),
            array(
                'watermark', // name
                null, // shortcut
                InputOption::VALUE_REQUIRED, // mode
                'Watermark Id or Name', // description
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        $ee = ee();

        $data = array(
            'site_id' => $ee->config->item('site_id'),
            'title' => $this->argument('title'),
            'short_name' => $this->argument('short_name'),
            'upload_location_id' => $this->transformKeyToId('upload_pref', $this->argument('upload_location')),
            'resize_type' => $this->option('resize_type'),
            'width' => $this->option('width'),
            'height' => $this->option('height'),
            'watermark_id' => $this->option('watermark')
        );

        $ee->db->insert('exp_file_dimensions', $data);

        if($ee->db->insert_id() == 0) {
            $this->error($ee->db->_error_message());
            return;
        }

        $this->comment('File upload manipulation '. $this->argument('short_name').' created.');
    }

    /**
     * {@inheritdoc}
     */
    public function getExamples()
    {
        return array(
            'Create an upload preference' => 'feature_image Blog',
        );
    }
}
