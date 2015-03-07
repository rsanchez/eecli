<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;
use Symfony\Component\Console\Input\InputOption;

class CreateFieldPlayaCommand extends AbstractCreateFieldCommand implements HasExamples
{
    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a Playa field.';

    /**
     * {@inheritdoc}
     */
    protected function getFieldtype()
    {
        return 'playa';
    }

    /**
     * {@inheritdoc}
     */
    protected function getFieldtypeOptions()
    {
        return array(
            array(
                'site',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'ID of site(s) to relate (Leave blank to allow all)',
            ),
            array(
                'channel',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'ID or name of channel(s) to relate (Leave blank to allow all)',
            ),
            array(
                'expired',
                null,
                InputOption::VALUE_NONE,
                'Show expired entries',
            ),
            array(
                'future',
                null,
                InputOption::VALUE_NONE,
                'Show future entries',
            ),
            array(
                'editable',
                null,
                InputOption::VALUE_NONE,
                'Show entries that are editable by the current user',
            ),
            array(
                'category',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'ID of category(s) to show (Leave blank to allow all)',
            ),
            array(
                'author',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'ID of author(s) to show (Leave blank to allow all)',
            ),
            array(
                'member_group',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'ID of authored by member group(s) to show (Leave blank to allow all)',
            ),
            array(
                'status',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Stasus(es) to show (Leave blank to allow all)',
            ),
            array(
                'limit',
                null,
                InputOption::VALUE_REQUIRED,
                'Limit',
                100,
            ),
            array(
                'order_by',
                null,
                InputOption::VALUE_REQUIRED,
                'title or entry_date',
                'title',
            ),
            array(
                'sort',
                null,
                InputOption::VALUE_REQUIRED,
                'asc or desc',
                'asc',
            ),
            array(
                'multiple',
                null,
                InputOption::VALUE_NONE,
                'Allow multiple relationships?',
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getFieldtypeSettings()
    {
        $channelIds = array();

        foreach ($this->option('channel') as $channelId) {
            if (! is_numeric($channelId)) {
                $query = ee()->db->select('channel_id')
                    ->where('channel_name', $channelId)
                    ->limit(1)
                    ->get('channels');

                if ($query->num_rows() > 0) {
                    $channelId = $query->row('channel_id');
                }

                $query->free_result();
            }

            $channelIds[] = $channelId;
        }

        return array(
            'playa' => array(
                'sites' => $this->option('site') ?: array('any'),
                'channels' => $channelIds ?: array('any'),
                'expired' => $this->option('expired') ? 'y' : 'n',
                'future' => $this->option('future') ? 'y' : 'n',
                'editable' => $this->option('editable') ? 'y' : 'n',
                'cats' => $this->option('category') ?: array('any'),
                'authors' => $this->option('author') ?: array('any'),
                'member_groups' => $this->option('member_group') ?: array('any'),
                'statuses' => $this->option('status') ?: array('any'),
                'limit' => $this->option('limit'),
                'limitby' => 'newest',
                'orderby' => $this->option('order_by'),
                'sort' => strtoupper($this->option('sort')),
                'multi' => $this->option('multiple') ? 'y' : 'n',
            ),
        );
    }

    protected function getFieldtypeOptionExamples()
    {
        return array(
            'site' => '1',
            'channel' => '1',
            'category' => '1',
            'author' => '1',
            'member_group' => '1',
            'status' => 'open',
        );
    }

    public function getExamples()
    {
        return array(
            'Create a Playa field in field group 1' => '"Your Field Name" your_field_name 1',
            'Create a Playa field with multiple channels' => '--channel=1 --channel=blog "Your Field Name" your_field_name 1',
            'Create a Playa field with multiple statuses' => '--status=closed --status=open "Your Field Name" your_field_name 1',
            'Create a Playa field with multiple selection' => '--multiple "Your Field Name" your_field_name 1',
        );
    }
}
