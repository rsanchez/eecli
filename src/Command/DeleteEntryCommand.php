<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class DeleteEntryCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'delete:entry';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Delete an entry';

    /**
     * {@inheritdoc}
     */
    protected function getArguments()
    {
        return array(
            array(
                'entry', // name
                InputArgument::REQUIRED, // mode
                'The entry_id, url_title, or title of an entry', // description
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

        ee()->db->select('entry_id,title')
            ->from('channel_titles')
            ->where('site_id', $siteId)
            ->where($type, $name);
        if ($type == "url_title"){
            ee()->db->or_where('title', $name);
        }
        $query = ee()->db->get();

        if ($query->num_rows() === 0){
            throw new \RuntimeException("This entry $name was not found");
        }

        if ($query->num_rows() > 1){
            throw new \RuntimeException("There were multiple entries with $name found");
        }

        ee()->load->library('api');
        ee()->api->instantiate('channel_entries');
        $entry_id = $query->row('entry_id');
        $title = $query->row('title');

        //set group id to be a super admin
        ee()->session->userdata['group_id'] = '1';
        ee()->session->userdata['can_delete_all_entries'] = 'y';

        $delete = ee()->api_channel_entries->delete_entry((int) $entry_id);

        //BUG for some reason this doesn't fire
        if ($delete){
            $this->info("$title entry was deleted");
        }else{
            $this->info("$title entry was not able to be deleted");
        }
    }
}
