<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

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
    protected function getOptions()
    {
        return array(
            array(
                'force', // name
                'f', // shortcut
                InputOption::VALUE_NONE, // mode
                'Do not ask for confirmation before deleting', // description
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

        if (! $this->option('force') && ! $this->confirm('Are you sure you want to delete? [Yn]', true)) {
            $this->error('Did not delete template group(s): '.implode(' ', $templateGroups));

            return;
        }

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
