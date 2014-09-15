<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class DeleteGlobalVariableCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'delete:global_variable';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Delete a global variable.';

    /**
     * {@inheritdoc}
     */
    protected function getArguments()
    {
        return array(
            array(
                'name', // name
                InputArgument::IS_ARRAY | InputArgument::REQUIRED, // mode
                'The name of the global variable(s) to delete.', // description
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        $names = $this->argument('name');

        $siteId = ee()->config->item('site_id');
        $siteName = ee()->config->item('site_short_name');

        $query = ee()->db->select('variable_id, variable_name, variable_data')
            ->where('site_id', $siteId)
            ->where_in('variable_name', $names)
            ->get('global_variables');

        $globalVariables = array();

        foreach ($query->result() as $row) {
            $globalVariables[$row->variable_name] = $row;
        }

        $query->free_result();

        $hasLowVariables = array_key_exists('Low_variables_ext', ee()->extensions->version_numbers);

        foreach ($names as $name) {
            if (! isset($globalVariables[$name])) {
                $this->error('Global variable '.$name.' not found.');

                continue;
            }

            $globalVariable = $globalVariables[$name];

            if ($hasLowVariables && ee()->db->where('variable_id', $globalVariable->variable_id)->count_all_results('low_variables') > 0) {
                $this->error('Could not delete Low Variable '.$name.'.');

                continue;
            }

            ee()->db->delete('global_variables', array('variable_id' => $globalVariable->variable_id));

            ee()->extensions->call('eecli_delete_global_variable', $globalVariable->variable_id, $globalVariable->variable_name, $globalVariable->variable_data, $siteId, $siteName);

            $this->info('Global variable '.$name.' deleted.');
        }
    }
}
