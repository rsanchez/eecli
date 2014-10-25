<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class CreateFieldRelationshipCommand extends AbstractCreateFieldCommand implements HasExamples
{
    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a Relationships field.';

    /**
     * {@inheritdoc}
     */
    protected function getFieldtype()
    {
        return 'relationship';
    }

    /**
     * {@inheritdoc}
     */
    protected function getFieldtypeOptions()
    {
        return array(
            array(
                'channel',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'ID of channel(s) to relate (Leave blank to allow all)',
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
                100
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
        $authorIds = $this->option('author');
        $groupIds = $this->option('member_group');

        $authors = array();

        if ($authorIds || $groupIds) {
            foreach ($authorIds as $authorId) {
                $authors[] = 'm_'.$authorId;
            }
            foreach ($groupIds as $groupId) {
                $authors[] = 'g_'.$groupId;
            }
        } else {
            $authors[] = '--';
        }

        return array(
            'relationship_channels' => $this->option('channel') ?: array('--'),
            'relationship_expired' => $this->option('expired'),
            'relationship_future' => $this->option('future'),
            'relationship_categories' => $this->option('category') ?: array('--'),
            'relationship_authors' => $authors,
            'relationship_statuses' => $this->option('status') ?: array('--'),
            'relationship_limit' => $this->option('limit'),
            'relationship_order_field' => $this->option('order_by'),
            'relationship_order_dir' => $this->option('sort'),
            'relationship_allow_multiple' => $this->option('multiple'),
        );
    }

    protected function getFieldtypeOptionExamples()
    {
        return array(
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
            'Create a Relationships field in field group 1' => '"Your Field Name" your_field_name 1',
            'Create a Relationships field with multiple channels' => '--channel=1 --channel=2 "Your Field Name" your_field_name 1',
            'Create a Relationships field with multiple statuses' => '--status=closed --status=open "Your Field Name" your_field_name 1',
            'Create a Relationships field with multiple selection' => '--multiple "Your Field Name" your_field_name 1',
        );
    }
}
