<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class CreateFieldGroupCommand extends Command implements HasExamples
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'create:field_group';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a field group.';

    /**
     * {@inheritdoc}
     */
    protected function getArguments()
    {
        return array(
            array(
                'name', // name
                InputArgument::REQUIRED, // mode
                'The name of the field group.', // description
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

        ee()->field_group_update();

        if (ee()->output->getErrorMessage()) {
            $this->error(ee()->output->getErrorMessage());

            return;
        }

        $query = ee()->db->select('group_id')
            ->where('group_name', $name)
            ->where('site_id', ee()->config->item('site_id'))
            ->get('field_groups');

        $this->info(sprintf('Field group %s (%s) created.', $name, $query->row('group_id')));

        $query->free_result();
    }

    public function getExamples()
    {
        return array(
            'Create a field group' => 'Blog',
        );
    }
}
