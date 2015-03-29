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

        $instance = $this->getApplication()->newControllerInstance('\\eecli\\CodeIgniter\\Controller\\DesignController');

        $instance->load->model('template_model');

        foreach ($names as $groupName) {
            $_POST = array(
                'group_name' => $groupName,
                'is_site_default' => $this->option('default') ? 'y' : 'n',
                'duplicate_group' => false,
            );

            $instance->new_template_group();

            if ($this->getApplication()->checkForErrors()) {
                continue;
            }

            $query = ee()->db->select('group_id')
                ->where('site_id', ee()->config->item('site_id'))
                ->where('group_name', $groupName)
                ->limit(1)
                ->get('template_groups');

            $groupId = $query->row('group_id');

            $query->free_result();

            if (ee()->config->item('save_tmpl_files') === 'y' && ee()->config->item('tmpl_file_basepath')) {
                // find the newly created index template
                $query = ee()->db->select('template_id')
                    ->where('site_id', ee()->config->item('site_id'))
                    ->where('group_id', $groupId)
                    ->where('template_name', 'index')
                    ->limit(1)
                    ->get('templates');

                $templateId = $query->row('template_id');

                $query->free_result();

                // create the template file for the index template
                $fileCreated = $instance->update_template_file(array(
                    'template_group' => $groupName,
                    'template_id' => $templateId,
                    'template_name' => 'index',
                    'template_type' => 'webpage',
                    'template_data' => '',
                ));

                // set the index template to save to file
                ee()->db->update('templates', array('save_template_file' => 'y'), array('template_id' => $templateId));

                if (! $fileCreated) {
                    $path = ee()->config->slash_item('tmpl_file_basepath').ee()->config->slash_item('site_short_name').$groupName.'.group/';

                    $this->error(sprintf('Could not write to %s', $path));
                }
            }

            $this->comment(sprintf('Template group %s (%s) created.', $groupName, $groupId));
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
