<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;
use eecli\Command\Contracts\HasLongDescription;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CreateGlobalVariableCommand extends AbstractCommand implements HasExamples, HasLongDescription
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

        $contents = $this->argument('data');

        if ($this->option('stdin')) {
            $contents = '';

            $handle = fopen('php://stdin', 'r');

            while (($buffer = fgets($handle, 4096)) !== false) {
                $contents .= $buffer;
            }
        }

        $tempContents = $contents ? $contents : '{!--TEMP--}';

        $this->getApplication()->newControllerInstance('\\eecli\\CodeIgniter\\Controller\\DesignController');

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

    public function getLongDescription()
    {
        return 'Create a global variable. When you have [Sync Snippets](https://github.com/rsanchez/sync_snippets) installed and configured, this command will write a global variable file as well.';
    }

    public function getExamples()
    {
        return array(
            'Create a blank global variable' => 'your_global_variable_name',
            'Create a global variable with content' => 'your_global_variable_name "your global variable content"',
            'Pipe in content' => '--stdin your_global_variable_name',
        );
    }
}
