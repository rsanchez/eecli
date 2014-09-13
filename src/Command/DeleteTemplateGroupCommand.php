<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class DeleteTemplateGroupCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'delete:template_group';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Delete one or more template groups.';

    /**
     * {@inheritdoc}
     */
    protected function getArguments()
    {
        return array(
            array(
                'template_group', // name
                InputArgument::IS_ARRAY | InputArgument::REQUIRED, // mode
                'Template group(s) (ex. news blog)', // description
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        $templateGroups = $this->argument('template_group');

        $this->getApplication()->newInstance('\\eecli\\CodeIgniter\\Controller\\DesignController');

        foreach ($templateGroups as $groupName) {

            $query = ee()->db->select('group_id')
                ->where('group_name', $groupName)
                ->get('template_groups');

            if ($query->num_rows() === 0) {
                $this->error('Template group '.$groupName.' not found.');
            } else {
                $_POST = array(
                    'group_id' => $query->row('group_id'),
                );

                ee()->template_group_delete();

                $this->info('Template group '.$groupName.' deleted.');
            }

            $query->free_result();
        }
    }
}
