<?php

namespace eecli\Command;

use Symfony\Component\Console\Input\InputOption;

class ShowFieldGroupsCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'show:field_groups';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Display a list of field groups.';

    /**
     * {@inheritdoc}
     */
    protected function getOptions()
    {
        return array(
            array(
                'channel', // name
                null, // shortcut
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, // mode
                'Limit results to the specified channel short name', // description
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        ee()->db->from('field_groups');

        ee()->db->where('field_groups.site_id', ee()->config->item('site_id'));

        $headers = array('Group ID');

        ee()->db->select('field_groups.group_id');

        $headers[] = 'Group Name';

        ee()->db->select('field_groups.group_name');

        $headers[] = 'Channels';

        ee()->db->select('GROUP_CONCAT(channel_name SEPARATOR ";") AS channels');

        ee()->db->join('channels', 'channels.field_group = field_groups.group_id', 'left');
        ee()->db->group_by('field_groups.group_id');

        if ($this->option('channel')) {

            ee()->db->where_in('channels.channel_name', $this->option('channel'));
        }

        $query = ee()->db->get();

        $results = $query->result_array();

        // alter the results and replace the semicolon with commas as CI driver doesn't like
        // commas in DB selects and its best not to deactivate escaping and sanitising functions
        $results = array_map(function($a) {
            return array_merge($a, array(
                'channels' => str_replace(';', ',', $a['channels']),
            ));
        }, $results);

        $query->free_result();

        $this->table($headers, $results);
    }
}
