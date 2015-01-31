<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ShowActionsCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'show:actions';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Display a list of module actions.';

    /**
     * {@inheritdoc}
     */
    protected function getArguments()
    {
        return array(
            array(
                'action_id', // name
                InputArgument::OPTIONAL, // mode
                'Display a specific action', // description
                null, // default value
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
                'module', // name
                null, // shortcut
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, // mode
                'Limit results to the specified module', // description
            ),
            array(
                'class', // name
                null, // shortcut
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, // mode
                'Limit results to the specified module class', // description
            ),
            array(
                'method', // name
                null, // shortcut
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, // mode
                'Limit results to the specified method', // description
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        if ($this->argument('action_id')) {
            ee()->db->where('action_id', $this->argument('action_id'));
        }

        $classes = $this->option('class') ?: [];

        if ($modules = $this->option('module')) {
            foreach ($modules as $module) {
                $classes[] = ucfirst($module);
                $classes[] = ucfirst($module).'_mcp';
            }
        }

        if ($classes) {
            ee()->db->where_in('class', $classes);
        }

        if ($methods = $this->option('method')) {
            ee()->db->where_in('method', $methods);
        }

        $query = ee()->db->order_by('action_id')
            ->get('actions');

        if ($query->num_rows() === 0) {
            if ($actionId) {
                $this->warning('There was no action found with the specified action_id.');
            } else {
                $this->warning('There were no actions found.');
            }
        } else {
            $results = $query->result_array();

            $headers = array('ID', 'Class', 'Method');

            if (array_key_exists('csrf_exempt', $results[0])) {
                $headers[] = 'CSRF Exempt';
            }

            $this->table($headers, $results);
        }

        $query->free_result();
    }
}
