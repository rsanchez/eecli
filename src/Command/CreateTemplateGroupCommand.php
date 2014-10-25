<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;
use eecli\Command\Contracts\HasLongDescription;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CreateTemplateGroupCommand extends Command implements HasExamples, HasLongDescription
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'create:template_group';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Create one or more template groups.';

    /**
     * {@inheritdoc}
     */
    protected function getArguments()
    {
        return array(
            array(
                'name', // name
                InputArgument::IS_ARRAY | InputArgument::REQUIRED, // mode
                'Template group name (ex. site blog news)', // description
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
                'default', // name
                'd', // shortcut
                InputOption::VALUE_NONE, // mode
                'Set as site default.', // description
                null, // default value
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        $names = $this->argument('name');

        $this->getApplication()->newInstance('\\eecli\\CodeIgniter\\Controller\\DesignController');

        ee()->load->model('template_model');

        foreach ($names as $groupName) {

            // if this is default turn off the other defaults
            /*
            if ($this->option('default')) {
                ee()->db->update('templates', array(
                    'is_site_default' => 'n',
                ), array(
                    'site_id' => ee()->config->item('site_id'),
                ));
            }
            */

            $_POST = array(
                'group_name' => $groupName,
                'is_site_default' => $this->option('default') ? 'y' : 'n',
                'duplicate_group' => false,
            );

            ee()->new_template_group();

            if (ee()->form_validation->_error_messages) {
                foreach (ee()->form_validation->_error_messages as $error) {
                    $this->error($error);
                }

                continue;
            }

            $this->comment('Template group '.$groupName.' created.');
        }
    }

    public function getLongDescription()
    {
        return 'Create a new template group. This will also create an index template in the new group(s).';
    }

    public function getExamples()
    {
        return array(
            'Create a template' => 'site',
            'Multiple groups' => 'site news blog',
            'Create the default group' => '--default site',
        );
    }
}
