<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;
use eecli\Command\Contracts\HasLongDescription;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class DeleteEntryCommand extends Command implements HasExamples, HasLongDescription
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'delete:entry';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Delete an entry.';

    /**
     * {@inheritdoc}
     */
    protected function getArguments()
    {
        return array(
            array(
                'entry', // name
                InputArgument::REQUIRED, // mode
                'The entry_id or url_title of an entry', // description
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
        $name = $this->argument('entry');

        $siteId = ee()->config->item('site_id');
        $siteName = ee()->config->item('site_short_name');

        $type = is_numeric($name) ? 'entry_id' : 'url_title';

        $query = ee()->db->select('entry_id, title')
            ->from('channel_titles')
            ->where('site_id', $siteId)
            ->where($type, $name)
            ->get();

        $name = is_numeric($name) ? $name : "“{$name}”";

        if ($query->num_rows() === 0) {
            $this->error(sprintf('Entry %s not found.', $name));

            return;
        }

        if ($query->num_rows() > 1) {
            throw new \RuntimeException("There were multiple entries with $name found");
        }

        ee()->load->library(array('api', 'stats'));
        ee()->api->instantiate('channel_entries');

        $entry_id = $query->row('entry_id');
        $title = $query->row('title');

        $query->free_result();

        //set group id to be a super admin
        ee()->session->userdata['group_id'] = '1';
        ee()->session->userdata['can_delete_all_entries'] = 'y';

        if (! $this->option('force') && ! $this->confirm('Are you sure you want to delete? [Yn]', true)) {
            $this->error(sprintf('Did not delete entry “%s” (%s)', $title, $entry_id));

            return;
        }

        $delete = ee()->api_channel_entries->delete_entry((int) $entry_id);

        if ($delete) {
            $this->info(sprintf('Entry “%s” (%s) deleted.', $title, $entry_id));
        } else {
            foreach (ee()->api_channel_entries->errors as $error) {
                $this->error($error);
            }
        }
    }

    public function getLongDescription()
    {
        return 'Delete an entry by entering in an entry_id or url_title. You will be asked to confirm that you want to delete the specified entry, unless you use the `--force` option.';
    }

    public function getExamples()
    {
        return array(
            'Delete an entry by the entry_id' => '123',
            'Delete an entry by the url_title' => 'entry_be_gone',
            'Delete an entry by the entry_id without confirmation' => '--force 123',
        );
    }
}
