<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;
use eecli\Command\Contracts\HasLongDescription;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class DeleteSnippetCommand extends AbstractCommand implements HasExamples, HasLongDescription
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'delete:snippet';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Delete a snippet.';

    /**
     * {@inheritdoc}
     */
    protected function getArguments()
    {
        return array(
            array(
                'name', // name
                InputArgument::IS_ARRAY | InputArgument::REQUIRED, // mode
                'The name of the snippet(s) to delete.', // description
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
                'Delete a global snippet.', // description
                null, // default value
            ),
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

        $siteId = $this->option('global') ? 0 : ee()->config->item('site_id');
        $siteName = $this->option('global') ? 'global_snippets' : ee()->config->item('site_short_name');

        if (! $this->option('force') && ! $this->confirm('Are you sure you want to delete? [Yn]', true)) {
            $this->error('Did not delete snippet(s): '.implode(' ', $names));

            return;
        }

        $query = ee()->db->select('snippet_id, snippet_name, snippet_contents')
            ->where('site_id', $siteId)
            ->where_in('snippet_name', $names)
            ->get('snippets');

        $snippets = array();

        foreach ($query->result() as $row) {
            $snippets[$row->snippet_name] = $row;
        }

        $query->free_result();

        foreach ($names as $name) {
            if (! isset($snippets[$name])) {
                $this->error('Snippet '.$name.' not found.');

                continue;
            }

            $snippet = $snippets[$name];

            ee()->db->delete('snippets', array('snippet_id' => $snippet->snippet_id));

            ee()->extensions->call('eecli_delete_snippet', $snippet->snippet_id, $snippet->snippet_name, $snippet->snippet_contents, $siteId, $siteName);

            $this->info('Snippet '.$name.' deleted.');
        }
    }

    public function getLongDescription()
    {
        return "Delete one or more snippets. You will be asked to confirm that you want to delete the specified snippet(s), unless you use the `--force` option.\n\nWhen you have [Sync Snippets](https://github.com/rsanchez/sync_snippets) installed and configured, this command will delete the snippet file as well.";
    }

    public function getExamples()
    {
        return array(
            'Delete a snippet' => 'your_snippet_name',
            'Delete a snippet accessible to all sites' => '--global your_snippet_name',
            'Delete multiple snippets' => 'your_snippet_name your_other_snippet_name',
            'Delete a snippet without confirmation' => '--force your_snippet_name',
        );
    }
}
