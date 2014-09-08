<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CreateChannelCommand extends Command
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
                'channel_name',
                InputArgument::REQUIRED,
                'What is the short name of the channel? (ex. blog_articles)',
            ),
            array(
                'channel_title',
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
                InputOption::VALUE_OPTIONAL,
                'Which field group do you want to assign this channel to?',
            ),
            array(
                'status_group',
                's',
                InputOption::VALUE_OPTIONAL,
                'Which status group do you want to assign this channel to?',
            ),
            array(
                'cat_group',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Which cat group(s) do you want to assign this channel to? Separate multiple with | char.',
            ),
            array(
                'channel_url',
                null,
                InputOption::VALUE_OPTIONAL,
                'What is the url for this channel?',
                '',
            ),
            array(
                'channel_description',
                null,
                InputOption::VALUE_OPTIONAL,
                'What is the description for this channel?',
            ),
            array(
                'default_entry_title',
                null,
                InputOption::VALUE_OPTIONAL,
                'What is the default entry title for this channel?',
            ),
            array(
                'url_title_prefix',
                null,
                InputOption::VALUE_OPTIONAL,
                'What is the URL Title prefix for this channel?',
            ),
            array(
                'deft_status',
                null,
                InputOption::VALUE_OPTIONAL,
                'What is the default status for this channel?',
                'open',
            ),
            array(
                'deft_category',
                null,
                InputOption::VALUE_OPTIONAL,
                'What is the default category ID for this channel?',
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        ee()->load->model('channel_model');

        $channel_name = $this->argument('channel_name');

        $query = ee()->db->where('channel_name', $channel_name)
            ->where('site_id', ee()->config->item('site_id'))
            ->get('channels');

        if ($query->num_rows() > 0) {
            throw new \RuntimeException("The channel \"{$channel_name}\" already exists.");
        }

        $query->free_result();

        $data = array(
            'channel_name' => $channel_name,
            'channel_title' => $this->argument('channel_title') ?: ucwords(str_replace('_', ' ', $channel_name)),
            'channel_url' => $this->option('channel_url'),
            'channel_description' => $this->option('channel_description'),
            'default_entry_title' => $this->option('default_entry_title'),
            'url_title_prefix' => $this->option('url_title_prefix'),
            'deft_status' => $this->option('deft_status'),
            'deft_category' => $this->option('deft_category'),
            'field_group' => $this->option('field_group'),
            'status_group' => $this->option('status_group'),
            'cat_group' => $this->option('field_group'),
            'channel_lang' => ee()->config->item('xml_lang'),
            'site_id' => ee()->config->item('site_id'),
        );

        if (! $data['field_group']) {
            //if there is only one field group assign it, otherwise for now leave unassigned
            $query = ee()->db->select('group_id')
                ->where('site_id', ee()->config->item('site_id'))
                ->get('field_groups');

            if ($query->num_rows() === 1) {
                $data['field_group'] = $query->row('group_id');
            }

            $query->free_result();
        }

        if (! $data['status_group']) {
            // trying to find the open/closed status group
            $query = ee()->db->select('group_id')
                ->where('(SELECT COUNT(*) FROM exp_statuses WHERE exp_statuses.group_id = exp_status_groups.group_id) = 2', null, false)
                ->where('site_id', ee()->config->item('site_id'))
                ->order_by('group_id', 'asc')
                ->limit(1)
                ->get('status_groups');

            if ($query->num_rows() > 0) {
                $data['status_group'] = $query->row('group_id');
            }

            $query->free_result();
        }

        ee()->channel_model->create_channel($data);

        $this->info("New channel {$channel_name} created");
    }
}
