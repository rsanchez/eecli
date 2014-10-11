<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class DeleteEntryCommand extends Command
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

        if ($query->num_rows() === 0) {
            throw new \RuntimeException("The entry $name was not found");
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
            $this->error('Did not delete entry '.$title);

            return;
        }

        $delete = ee()->api_channel_entries->delete_entry((int) $entry_id);

        if ($delete) {
            $this->info("$title entry was deleted");
        } else {
            foreach (ee()->api_channel_entries->errors as $error) {
                $this->error($error);
            }
        }
    }
}
