<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CreateGlobalVariableCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'create:global_variable';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a global variable.';

    /**
     * {@inheritdoc}
     */
    protected function getArguments()
    {
        return array(
            array(
                'name', // name
                InputArgument::REQUIRED, // mode
                'Name of global variable.', // description
            ),
            array(
                'data', // name
                InputArgument::OPTIONAL, // mode
                'Content of global variable.', // description
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
                'stdin', // name
                null, // shortcut
                InputOption::VALUE_NONE, // mode
                'Use stdin as global variable contents.', // description
                null, // default value
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        $name = $this->argument('name');

        $siteId = ee()->config->item('site_id');
        $siteName = ee()->config->item('site_short_name');

        $contents = $this->argument('contents');

        if ($this->option('stdin')) {
            $contents = '';

            $handle = fopen('php://stdin', 'r');

            while (($buffer = fgets($handle, 4096)) !== false) {
                $contents .= $buffer;
            }
        }

        $tempContents = $contents ? $contents : '{!--TEMP--}';

        $this->getApplication()->newInstance('\\eecli\\CodeIgniter\\Controller\\DesignController');

        $_POST = array(
            'variable_name' => $name,
            'variable_data' => $tempContents,
        );

        ee()->global_variables_create();

        $query = ee()->db->where('variable_name', $name)
            ->where('site_id', $siteId)
            ->get('global_variables');

        // restore the blank contents
        if (! $contents) {
            ee()->db->update('global_variables', array('variable_data' => ''), array('variable_id' => $query->row('variable_id')));
        }

        ee()->extensions->call('eecli_create_global_variable', $query->row('variable_id'), $query->row('variable_name'), $query->row('variable_data'), $siteId, $siteName);

        $query->free_result();

        $this->info('Global variable '.$name.' created.');
    }
}
