<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CreateUploadPrefCommand extends Command implements HasExamples
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'create:upload_pref';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a new file upload destination.';

    /**
     * {@inheritdoc}
     */
    protected function getArguments()
    {
        return array(
            array(
                'name', // name
                InputArgument::REQUIRED, // mode
                'Descriptive name', // description
            ),
            array(
                'server_path', // name
                InputArgument::REQUIRED, // mode
                'Server path to the upload directory', // description
            ),
            array(
                'url', // name
                InputArgument::REQUIRED, // mode
                'URL to the upload directory', // description
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getOptions()
    {
        return array(
            array(
                'images_only', // name
                null, // shortcut
                InputOption::VALUE_NONE, // mode
                'Only allow images', // description
            ),
            array(
                'max_size', // name
                null, // shortcut
                InputOption::VALUE_REQUIRED, // mode
                'Max file size in bytes', // description
                '', // default value
            ),
            array(
                'max_width', // name
                null, // shortcut
                InputOption::VALUE_REQUIRED, // mode
                'Max width in pixels', // description
                '', // default value
            ),
            array(
                'max_height', // name
                null, // shortcut
                InputOption::VALUE_REQUIRED, // mode
                'Max height in pixels', // description
                '', // default value
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        $instance = $this->getApplication()->newControllerInstance('\\eecli\\CodeIgniter\\Controller\\ContentFilesController');

        $instance->load->library('form_validation');
        $instance->load->model('file_model');

        $name = $this->argument('name');

        $_POST = array(
            'name' => $name,
            'server_path' => $this->argument('server_path'),
            'url' => $this->argument('url'),
            'allowed_types' => $this->option('images_only') ? 'img' : 'all',
            'max_size' => $this->option('max_size'),
            'max_width' => $this->option('max_width'),
            'max_height' => $this->option('max_height'),
            'properties' => '',
            'pre_format' => '',
            'post_format' => '',
            'file_properties' => '',
            'file_pre_format' => '',
            'file_post_format' => '',
            'size_short_name_2' => '',
            'size_resize_type_2' => 'none',
            'size_width_2' => '',
            'size_height_2' => '',
            'size_watermark_id_2' => '0',
            'submit' => 'Submit',
        );

        $query = $instance->db->where('site_id', $instance->config->item('site_id'))
            ->where('group_id >', 4)
            ->get('member_groups');

        foreach ($query->result() as $row) {
            $_POST['access_'.$row->group_id] = 'n';
        }

        $query->free_result();

        $instance->edit_upload_preferences();

        $this->getApplication()->checkForErrors(true);

        $this->comment('File upload destination '.$name.' created.');
    }

    /**
     * {@inheritdoc}
     */
    public function getExamples()
    {
        return array(
            'Create a file upload destination with default options' => '"My Files" ./uploads/files /uploads/files/',
            'Create a file upload destination with images only' => '--images_only "My Files" ./uploads/files /uploads/files/',
            'Create a file upload destination with max dimensions' => '--images_only --max_width=1024 --max_height=1024 "My Files" ./uploads/files /uploads/files/',
        );
    }
}
