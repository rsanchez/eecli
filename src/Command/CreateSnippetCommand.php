<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;
use eecli\Command\Contracts\HasLongDescription;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CreateSnippetCommand extends Command implements HasExamples, HasLongDescription
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'create:snippet';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a snippet.';

    /**
     * {@inheritdoc}
     */
    protected function getArguments()
    {
        return array(
            array(
                'name', // name
                InputArgument::REQUIRED, // mode
                'Name of snippet.', // description
            ),
            array(
                'contents', // name
                InputArgument::OPTIONAL, // mode
                'Content of snippet.', // description
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
                'global', // name
                null, // shortcut
                InputOption::VALUE_NONE, // mode
                'Create a global snippet.', // description
                null, // default value
            ),
            array(
                'stdin', // name
                null, // shortcut
                InputOption::VALUE_NONE, // mode
                'Use stdin as snippet contents.', // description
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

        $siteId = $this->option('global') ? 0 : ee()->config->item('site_id');
        $siteName = $this->option('global') ? 'global_snippets' : ee()->config->item('site_short_name');

        $contents = $this->argument('contents');

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
            'snippet_name' => $name,
            'snippet_contents' => $tempContents,
            'site_id' => $siteId,
        );

        ee()->snippets_update();

        if (ee()->output->getErrorMessage()) {
            $this->error(ee()->output->getErrorMessage());

            return;
        }

        $query = ee()->db->where('snippet_name', $name)
            ->get('snippets');

        // restore the blank contents
        if (! $contents) {
            ee()->db->update('snippets', array('snippet_contents' => ''), array('snippet_id' => $query->row('snippet_id')));
        }

        ee()->extensions->call('eecli_create_snippet', $query->row('snippet_id'), $query->row('snippet_name'), $query->row('snippet_contents'), $siteId, $siteName);

        $query->free_result();

        $this->info('Snippet '.$name.' created.');
    }

    public function getLongDescription()
    {
        return 'Create a new snippet. When you have [Sync Snippets](https://github.com/rsanchez/sync_snippets) installed and configured, this command will write a snippet file as well.';
    }

    public function getExamples()
    {
        return array(
            'Create a blank snippet' => 'your_snippet_name',
            'Create a snippet with content' => 'your_snippet_name "your snippet content"',
            'Pipe in content' => '--stdin your_snippet_name',
            'Create a snippet accessible to all sites' => '--global your_snippet_name',
        );
    }
}
