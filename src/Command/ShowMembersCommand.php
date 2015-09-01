<?php

namespace eecli\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ShowMembersCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'show:members';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Display a list of members.';

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
                'Limit results to the specified member group id', // description
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getArguments()
    {
        return array(
            array(
                'search', // name
                InputArgument::REQUIRED, // mode
                'Search email, username and screen_name for this string. Or member_id if numeric.', // description
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        ee()->db->from('members');

        $headers = array();

        ee()->db->select('members.member_id');
        $headers[] = 'ID';

        ee()->db->join('member_groups', 'member_groups.group_id = members.group_id');
        $table = ee()->db->dbprefix('member_groups');
        ee()->db->select("CONCAT('(', {$table}.group_id, ') ', {$table}.group_title) AS group_name", FALSE);
        $headers[] = 'Group';

        ee()->db->select('members.username');
        $headers[] = 'Username';

        ee()->db->select('members.screen_name');
        $headers[] = 'Screen Name';

        ee()->db->select('members.email');
        $headers[] = 'Email';

        if ($this->option('group_id')) {
            ee()->db->where_in('members.group_id', $this->option('group_id'));
        }

        $search = $this->argument('search');

        if ($search) {
            if (is_numeric($search)) {
                ee()->db->where('members.member_id', $search);
            } else {
                ee()->db->like('members.username', $search)
                    ->or_like('members.screen_name', $search)
                    ->or_like('members.email', $search);
            }
        }

        $sql = ee()->db->_compile_select();
        ee()->db->_reset_select();
        $this->output->writeln($sql);
        $query = ee()->db->query($sql);
        //$query = ee()->db->get();

        $results = $query->result_array();

        $query->free_result();

        $this->table($headers, $results);
    }
}
