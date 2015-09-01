<?php

namespace eecli\Command;

use Symfony\Component\Console\Input\InputOption;

class ShowFieldsCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'show:fields';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Display a list of channel fields.';

    /**
     * {@inheritdoc}
     */
    protected function getOptions()
    {
        return array(
            array(
                'group_id', // name
                null, // shortcut
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, // mode
                'Limit results to the specified field group id', // description
            ),
            array(
                'channel', // name
                null, // shortcut
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, // mode
                'Limit results to the specified channel short name', // description
            ),
            array(
                'type', // name
                null, // shortcut
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, // mode
                'Limit results to the specified fieldtype', // description
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        ee()->db->from('channel_fields');

        ee()->db->where('channel_fields.site_id', ee()->config->item('site_id'));

        $headers = array('ID');

        ee()->db->select('channel_fields.field_id');

        ee()->db->order_by('channel_fields.group_id', 'asc');

        ee()->db->order_by('channel_fields.field_order', 'asc');

        ee()->db->select('channel_fields.group_id');

        $headers[] = 'Group ID';

        ee()->db->join('field_groups', 'field_groups.group_id = channel_fields.group_id');

        ee()->db->select('field_groups.group_name');

        $headers[] = 'Group';

        if ($this->option('group_id')) {
            ee()->db->where_in('channel_fields.group_id', $this->option('group_id'));
        }

        if ($this->option('channel')) {
            ee()->db->join('channels', 'channels.field_group = channel_fields.group_id');

            ee()->db->where_in('channels.channel_name', $this->option('channel'));
        }

        if ($this->option('type')) {
            ee()->db->where_in('channel_fields.field_type', $this->option('type'));
        }

        ee()->db->select('channel_fields.field_name');
        $headers[] = 'Name';

        ee()->db->select('channel_fields.field_label');
        $headers[] = 'Label';

        ee()->db->select('channel_fields.field_type');
        $headers[] = 'Type';

        ee()->db->select('channel_fields.field_required');
        $headers[] = 'R';

        ee()->db->select('channel_fields.field_is_hidden');
        $headers[] = 'H';

        ee()->db->select('channel_fields.field_search');
        $headers[] = 'S';

        $query = ee()->db->get();

        $results = $query->result_array();

        $query->free_result();

        $this->table($headers, $results);
    }
}
