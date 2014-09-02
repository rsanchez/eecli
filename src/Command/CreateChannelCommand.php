<?php

namespace eecli\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateChannelCommand extends Command
{
    protected function configure()
    {
        $this->setName('craete:ee:channel');
        $this->setDescription('Creates An EE Channel');

        $this->addArgument(
            'channel',
            InputArgument::REQUIRED,
            'What is the channel name that you want to set'
        );
        $this->addArgument(
            'field_group',
            InputArgument::OPTIONAL,
            'Which channel field do you want to assign this channel to'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ee()->load->model('channel_model');
        ee()->load->helper('url');
        $channel_title= $input->getArgument('channel');
        $channel_name = url_title($channel_title);

        //mimic the functionality in admin_content channel_update() method
        $channel_url    = ee()->functions->fetch_site_index();
        $channel_lang   = ee()->config->item('xml_lang');

        //if there is only one field group assign it, otherwise for now leave unassigned
        ee()->db->select('group_id');
        ee()->db->where('site_id', ee()->config->item('site_id'));
        $query = ee()->db->get('field_groups');
        if ($query->num_rows() == 1)
        {
            $field_group = $query->row('group_id');
        }
        $site_id = ee()->config->item('site_id');
        $default_entry_title = '';
        $url_title_prefix = '';

        //get the necessary data and fill it in and run the channel model command
        $data = array(
            'channel_name'  => $channel_name,
            'channel_title' => $channel_title,
            'channel_url'   => $channel_url,
            'channel_lang'  => $channel_lang
        );
        var_dump($data);exit();
        //ee()->channel_model->create_channel($data);


        $output->writeln("<info>New Channel $data </info>");
    }
}

