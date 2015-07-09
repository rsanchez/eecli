<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class ShowCategoryGroupsCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'show:category_groups';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Display a list of category groups.';

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
        ee()->db->from('category_groups');

        ee()->db->where('category_groups.site_id', ee()->config->item('site_id'));

        $headers = array('Group ID');

        ee()->db->select('category_groups.group_id');

        $headers[] = 'Group Name';

        ee()->db->select('category_groups.group_name');

        $headers[] = 'Channels';

        ee()->db->select('GROUP_CONCAT(channel_name SEPARATOR ";") AS channels');

        // I know it looks bad but we have to drop the protect identifiers here to get the join working
        $existingProtectIdentifiers = ee()->db->_protect_identifiers;
        ee()->db->_protect_identifiers = false;
        ee()->db->join('channels', 'FIND_IN_SET(group_id, REPLACE(cat_group, "|", ",")) > 0', 'left');
        ee()->db->_protect_identifiers = $existingProtectIdentifiers;

        ee()->db->group_by('category_groups.group_id');

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

