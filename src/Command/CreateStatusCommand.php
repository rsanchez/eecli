<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;
use eecli\Command\Contracts\HasOptionExamples;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CreateStatusCommand extends Command implements HasExamples, HasOptionExamples
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'create:status';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a status.';

    /**
     * {@inheritdoc}
     */
    protected function getArguments()
    {
        return array(
            array(
                'status', // name
                InputArgument::REQUIRED, // mode
                'The name of the status.', // description
            ),
            array(
                'status_group', // name
                InputArgument::REQUIRED, // mode
                'The ID or name of the status group.', // description
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
                'color', // name
                'c', // shortcut
                InputOption::VALUE_REQUIRED, // mode
                'The color of the status.', // description
                null, // default value
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        $this->getApplication()->newInstance('\\eecli\\CodeIgniter\\Controller\\AdminContentController');

        $status = $this->argument('status');

        $group = $this->argument('status_group');

        if (is_numeric($group)) {
            $query = ee()->db->select('group_id')
                ->where('group_id', $group)
                ->get('status_groups');

            if ($query->num_rows() === 0) {
                throw new \RuntimeException('Invalid group ID.');
            }

            $groupId = $query->row('group_id');

            $query->free_result();
        } else {
            $query = ee()->db->select('group_id')
                ->where('group_name', $group)
                ->get('status_groups');

            if ($query->num_rows() === 0) {
                throw new \RuntimeException('Invalid group name.');
            }

            $groupId = $query->row('group_id');

            $query->free_result();
        }

        $color = $this->option('color');

        if ($color) {
            if (! preg_match('/^(#?)[0-9abcdefABCDEF]{6}$/', $color, $match)) {
                throw new \RuntimeException('Color must be a six digit hex string.');
            }

            $color = str_replace($match[1], '', $color);
        }

        $query = ee()->db->select('status_order')
            ->where('group_id', $groupId)
            ->order_by('status_order', 'desc')
            ->get('statuses');

        $_POST = array(
            'status_id' => '',
            'old_status' => '',
            'group_id' => $groupId,
            'status' => $status,
            'status_order' => $query->row('status_order') + 1,
            'highlight' => $color ?: '000000',
        );

        $query = ee()->db->select('group_id')
            ->where('group_id >', 4)
            ->where('site_id', ee()->config->item('site_id'))
            ->get('member_groups');

        foreach ($query->result() as $row) {
            $_POST['access_'.$row->group_id] = 'y';
        }

        ee()->status_update();

        if (ee()->output->getErrorMessage()) {
            $this->error(ee()->output->getErrorMessage());

            return;
        }

        $query = ee()->db->select('status_id')
            ->where('status', $status)
            ->get('statuses');

        $this->info(sprintf('Status %s (%s) created.', $status, $query->row('status_id')));

        $query->free_result();
    }

    public function getOptionExamples()
    {
        return array(
            'color' => '990000',
        );
    }

    public function getExamples()
    {
        return array(
            'Create a status in the specfied group (by ID)' => 'featured 1',
            'Create a status in the specified group (by name)' => 'draft your_group_name',
            'Create a status with a red color' => '--color="FF0000" featured 1',
        );
    }
}
