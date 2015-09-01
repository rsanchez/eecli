<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;
use Symfony\Component\Console\Input\InputArgument;

class CreateStatusGroupCommand extends Command implements HasExamples
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'create:status_group';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a status group.';

    /**
     * {@inheritdoc}
     */
    protected function getArguments()
    {
        return array(
            array(
                'name', // name
                InputArgument::OPTIONAL, // mode
                'The name of the status group', // description
                null, // default value
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        $this->getApplication()->newControllerInstance('\\eecli\\CodeIgniter\\Controller\\AdminContentController');

        $name = $this->argument('name');

        $_POST = array(
            'group_name' => $name,
        );

        ee()->status_group_update();

        if (ee()->output->getErrorMessage()) {
            $this->error(ee()->output->getErrorMessage());

            return;
        }

        $query = ee()->db->select('group_id')
            ->where('group_name', $name)
            ->get('status_groups');

        $this->info(sprintf('Status group %s (%s) created.', $name, $query->row('group_id')));

        $query->free_result();
    }

    public function getExamples()
    {
        return array(
            'Create a status group' => 'your_group_name',
        );
    }
}
