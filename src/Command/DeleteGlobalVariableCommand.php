<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;
use eecli\Command\Contracts\HasLongDescription;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class DeleteGlobalVariableCommand extends AbstractCommand implements HasExamples, HasLongDescription
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
        $names = $this->argument('name');

        $siteId = ee()->config->item('site_id');
        $siteName = ee()->config->item('site_short_name');

        if (! $this->option('force') && ! $this->confirm('Are you sure you want to delete? [Yn]', true)) {
            $this->error('Did not delete global variable(s): '.implode(' ', $names));

            return;
        }

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

    public function getLongDescription()
    {
        return "Delete one or more global variables. You will be asked to confirm that you want to delete the specified global variable(s), unless you use the `--force` option.\n\nWhen you have [Sync Snippets](https://github.com/rsanchez/sync_snippets) installed and configured, this command will delete the global variable file as well.";
    }

    public function getExamples()
    {
        return array(
            'Delete a global variable' => 'your_global_variable_name',
            'Delete multiple global variables' => 'your_global_variable_name your_other_global_variable_name',
            'Delete a global variable without confirmation' => '--force your_global_variable_name',
        );
    }
}
