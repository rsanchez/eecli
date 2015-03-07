<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;
use eecli\Command\Contracts\HasLongDescription;
use eecli\Command\Contracts\HasOptionExamples;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CreateChannelCommand extends Command implements HasExamples, HasOptionExamples, HasLongDescription
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'create:channel';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a channel.';

    /**
     * {@inheritdoc}
     */
    protected function getArguments()
    {
        return array(
            array(
                'name',
                InputArgument::REQUIRED,
                'What is the short name of the channel? (ex. blog_articles)',
            ),
            array(
                'title',
                InputArgument::OPTIONAL,
                'What is the title of the channel? (ex. Blog Articles)',
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
                'field_group',
                'f',
                InputOption::VALUE_REQUIRED,
                'Which field group (ID or name) do you want to assign this channel to?',
                '',
            ),
            array(
                'status_group',
                's',
                InputOption::VALUE_REQUIRED,
                'Which status group (ID or name) do you want to assign this channel to?',
                '',
            ),
            array(
                'cat_group',
                'c',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Which category group(s) (ID or name) do you want to assign this channel to? Separate multiple with comma.',
            ),
            array(
                'channel_url',
                null,
                InputOption::VALUE_REQUIRED,
                'What is the url for this channel?',
                '',
            ),
            array(
                'channel_description',
                null,
                InputOption::VALUE_REQUIRED,
                'What is the description for this channel?',
            ),
            array(
                'default_entry_title',
                null,
                InputOption::VALUE_REQUIRED,
                'What is the default entry title for this channel?',
            ),
            array(
                'url_title_prefix',
                null,
                InputOption::VALUE_REQUIRED,
                'What is the URL Title prefix for this channel?',
            ),
            array(
                'deft_status',
                null,
                InputOption::VALUE_REQUIRED,
                'What is the default status for this channel?',
                'open',
            ),
            array(
                'deft_category',
                null,
                InputOption::VALUE_REQUIRED,
                'What is the default category (ID or name) for this channel?',
            ),
            array(
                'new_field_group',
                null,
                InputOption::VALUE_NONE,
                'Do you wish to also create a new field group with the same name as the channel?',
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        $this->getApplication()->newControllerInstance('\\eecli\\CodeIgniter\\Controller\\AdminContentController');

        $name = $this->argument('name');

        $title = $this->argument('title') ?: ucwords(str_replace('_', ' ', $name));

        $fieldGroup = (string) $this->option('field_group');

        if ($fieldGroup) {
            // find the group ID by name
            if (! is_numeric($fieldGroup)) {
                $query = ee()->db->select('group_id')
                    ->where('group_name', $fieldGroup)
                    ->limit(1)
                    ->get('field_groups');

                if ($query->num_rows() > 0) {
                    $fieldGroup = $query->row('group_id');
                }

                $query->free_result();
            }
        } else {
            if ($this->option('new_field_group')) {
                ee()->load->model('field_model');

                if (ee()->field_model->is_duplicate_field_group_name($title)) {
                    $this->error("The field group \"{$title}\" already exists. Channel not created.");

                    return;
                }

                ee()->field_model->insert_field_group($title);

                $fieldGroup = ee()->db->insert_id();
            } else {
                //if there is only one field group assign it, otherwise for now leave unassigned
                $query = ee()->db->select('group_id')
                    ->where('site_id', ee()->config->item('site_id'))
                    ->get('field_groups');

                if ($query->num_rows() === 1) {
                    $fieldGroup = $query->row('group_id');
                }

                $query->free_result();
            }
        }

        $statusGroup = (string) $this->option('status_group');

        if ($statusGroup) {
            // find the group ID by name
            if (! is_numeric($statusGroup)) {
                $query = ee()->db->select('group_id')
                    ->where('group_name', $statusGroup)
                    ->limit(1)
                    ->get('status_groups');

                if ($query->num_rows() > 0) {
                    $statusGroup = $query->row('group_id');
                }

                $query->free_result();
            }
        } else {
            // trying to find the open/closed status group
            $query = ee()->db->select('group_id')
                ->where('(SELECT COUNT(*) FROM exp_statuses WHERE exp_statuses.group_id = exp_status_groups.group_id) = 2', null, false)
                ->where('site_id', ee()->config->item('site_id'))
                ->order_by('group_id', 'asc')
                ->limit(1)
                ->get('status_groups');

            if ($query->num_rows() > 0) {
                $statusGroup = $query->row('group_id');
            }

            $query->free_result();
        }

        $catGroups = array();

        foreach ($this->option('cat_group') as $catGroup) {
            // find the group ID by name
            if (! is_numeric($catGroup)) {
                $query = ee()->db->select('group_id')
                    ->where('group_name', $catGroup)
                    ->limit(1)
                    ->get('category_groups');

                if ($query->num_rows() > 0) {
                    $catGroup = $query->row('group_id');
                }

                $query->free_result();
            }

            $catGroups[] = $catGroup;
        }

        $_POST = array(
            'channel_title' => $title,
            'channel_name' => $name,
            'duplicate_channel_prefs' => '',
            'cat_group' => $catGroups,
            'status_group' => $statusGroup,
            'field_group' => $fieldGroup,
            'channel_prefs_submit' => 'Submit',
        );

        ee()->channel_add();

        $this->getApplication()->checkForErrors(true);

        $query = ee()->db->select('channel_id')
            ->where('channel_name', $name)
            ->get('channels');

        $channelId = $query->row('channel_id');

        $query->free_result();

        $deftCategory = $this->option('deft_category');

        // find the group ID by name
        if (! is_numeric($deftCategory)) {
            $query = ee()->db->select('cat_id')
                ->where('cat_url_title', $deftCategory)
                ->or_where('cat_name', $deftCategory)
                ->limit(1)
                ->get('categories');

            if ($query->num_rows() > 0) {
                $deftCategory = $query->row('cat_id');
            }

            $query->free_result();
        }

        ee()->db->where('channel_id', $channelId)->update('channels', array(
            'channel_url' => $this->option('channel_url'),
            'channel_description' => $this->option('channel_description'),
            'default_entry_title' => $this->option('default_entry_title'),
            'url_title_prefix' => $this->option('url_title_prefix'),
            'deft_status' => $this->option('deft_status'),
            'deft_category' => $deftCategory,
        ));

        $this->info(sprintf('Channel %s (%s) created.', $name, $channelId));
    }

    public function getLongDescription()
    {
        return 'Create a channel. Pass in a channel short name using underscores only and optionally pass in a channel title. If you exclude the channel title, one will be auto-generated from your channel short name.';
    }

    public function getExamples()
    {
        return array(
            'Create a channel with the short name test_channel' => 'test_channel',
            'Create a channel with the title Test Channel' => 'test_channel "Test Channel"',
            'Create a channel with field group 5' => '--field_group=5 test_channel',
            'Create a channel with field group "Blog"' => '--field_group="Blog" test_channel',
            'Create a channel with status group "Statuses"' => '--status_group="Statuses" test_channel',
            'Create a channel with category group 5 and 6' => '--cat_group="5,6" test_channel',
            'Create a channel with category group "Apparel" and "Accessories"' => '--cat_group="Apparel,Accessories" test_channel',
            'Create a channel with new field group with same title as channel' => '--new_field_group test_channel',
        );
    }

    public function getOptionExamples()
    {
        return array(
            'field_group' => '1',
            'status_group' => '1',
            'cat_group' => '1,2',
            'channel_url' => '/blog',
            'channel_description' => 'Your description here.',
            'default_entry_title' => 'Default Title',
            'url_title_prefix' => 'blog_',
            'deft_category' => '1',
        );
    }
}
