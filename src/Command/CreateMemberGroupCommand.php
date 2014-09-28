<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CreateMemberGroupCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'create:member_group';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a member group.';

    protected $defaults = array(
        'is_locked' => 'y',
        'can_view_online_system' => 'y',
        'can_view_offline_system' => 'n',
        'can_view_profiles' => 'n',
        'can_email_from_profile' => 'n',
        'can_edit_html_buttons' => 'n',
        'include_in_authorlist' => 'n',
        'include_in_memberlist' => 'n',
        'include_in_mailinglists' => 'n',
        'can_delete_self' => 'n',
        'mbr_delete_notify_emails' => '',
        'can_post_comments' => 'y',
        'exclude_from_moderation' => 'n',
        'can_search' => 'y',
        'search_flood_control' => '15',
        'can_send_private_messages' => 'n',
        'prv_msg_send_limit' => '20',
        'prv_msg_storage_limit' => '60',
        'can_attach_in_private_messages' => 'n',
        'can_send_bulletins' => 'n',
        'can_access_cp' => 'n',
        'can_access_content' => 'n',
        'can_access_publish' => 'n',
        'can_access_edit' => 'n',
        'can_access_files' => 'n',
        'can_access_design' => 'n',
        'can_access_addons' => 'n',
        'can_access_modules' => 'n',
        'can_access_extensions' => 'n',
        'can_access_accessories' => 'n',
        'can_access_plugins' => 'n',
        'can_access_fieldtypes' => 'n',
        'can_access_members' => 'n',
        'can_access_admin' => 'n',
        'can_access_sys_prefs' => 'n',
        'can_access_content_prefs' => 'n',
        'can_access_tools' => 'n',
        'can_access_comm' => 'n',
        'can_access_utilities' => 'n',
        'can_access_data' => 'n',
        'can_access_logs' => 'n',
        'can_admin_channels' => 'n',
        'can_admin_upload_prefs' => 'n',
        'can_admin_templates' => 'n',
        'can_admin_design' => 'n',
        'can_admin_members' => 'n',
        'can_admin_mbr_groups' => 'n',
        'can_admin_mbr_templates' => 'n',
        'can_delete_members' => 'n',
        'can_ban_users' => 'n',
        'can_admin_modules' => 'n',
        'can_send_email' => 'n',
        'can_email_member_groups' => 'n',
        'can_email_mailinglist' => 'y',
        'can_send_cached_email' => 'n',
        'can_view_other_entries' => 'n',
        'can_delete_self_entries' => 'n',
        'can_edit_other_entries' => 'n',
        'can_delete_all_entries' => 'n',
        'can_assign_post_authors' => 'n',
        'can_edit_categories' => 'n',
        'can_delete_categories' => 'n',
        'can_moderate_comments' => 'n',
        'can_view_other_comments' => 'n',
        'can_edit_own_comments' => 'n',
        'can_delete_own_comments' => 'n',
        'can_edit_all_comments' => 'n',
        'can_delete_all_comments' => 'n',
    );

    /**
     * {@inheritdoc}
     */
    protected function getArguments()
    {
        return array(
            array(
                'name', // name
                InputArgument::REQUIRED, // mode
                'Member group name', // description
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getOptions()
    {
        $options = array(
            array(
                'clone', // name
                'c', // shortcut
                InputOption::VALUE_OPTIONAL, // mode
                'Clone the specified member group id', // description
                null, // default value
            ),
            array(
                'description', // name
                'd', // shortcut
                InputOption::VALUE_OPTIONAL, // mode
                'The member group description', // description
                '', // default value
            ),
        );

        foreach ($this->defaults as $key => $value) {
            if ($value === 'y' || $value === 'n') {
                $options[] = array(
                    $key, // name
                    null, // shortcut
                    InputOption::VALUE_REQUIRED, // mode
                    'y or n', // description
                    null, // default value
                );
            } else {
                $options[] = array(
                    $key, // name
                    null, // shortcut
                    InputOption::VALUE_REQUIRED, // mode
                    '', // description
                    null, // default value
                );
            }
        }

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        $this->getApplication()->newInstance('\\eecli\\CodeIgniter\\Controller\\MembersController');

        ee()->load->helper(array('string', 'security'));
        ee()->load->library('stats');

        $name = $this->argument('name');

        $_POST = array(
            'clone_id' => '',
            'group_id' => '0',
            'site_id' => ee()->config->item('site_id'),
            'group_title' => $name,
            'group_description' => $this->option('description'),
        );

        $clone = $this->option('clone');

        if ($clone) {
            $query = ee()->db->where('group_id', $clone)
                ->get('member_groups');

            if ($query->num_rows() === 0) {
                throw new \RuntimeException('Invalid clone ID.');
            }

            foreach ($this->defaults as $key => $value) {
                $option = $this->option($key);

                if (is_null($option)) {
                    $_POST[$query->row('site_id').'_'.$key] = $query->row($key);
                } else {
                    if ($this->isOptionBool($value) && ! $this->isOptionBool($option)) {
                        throw new \RuntimeException(sprintf('Option %s should be y or n', $key));
                    }

                    $_POST[$query->row('site_id').'_'.$key] = $option;
                }
            }

            $query->free_result();

            $query = ee()->db->select('template_groups.site_id, template_groups.group_id, template_member_groups.group_id AS member_group')
                ->join('template_member_groups', 'template_groups.group_id = template_member_groups.template_group_id AND '.ee()->db->dbprefix('template_member_groups').'.`group_id` = '.ee()->db->escape($clone), 'left')
                ->get('template_groups');

            foreach ($query->result() as $row) {
                $_POST[$row->site_id.'_template_id_'.$row->group_id] = $row->member_group ? 'y' : 'n';
            }

            $query->free_result();

            $query = ee()->db->select('modules.module_id, module_member_groups.group_id')
                ->join('module_member_groups', 'module_member_groups.module_id = modules.module_id AND '.ee()->db->dbprefix('module_member_groups').'.`group_id` = '.ee()->db->escape($clone), 'left')
                ->get('modules');

            foreach ($query->result() as $row) {
                $_POST['module_id_'.$row->module_id] = $row->group_id ? 'y' : 'n';
            }

            $query->free_result();

            $query = ee()->db->select('channels.channel_id, channel_member_groups.group_id, channels.site_id')
                ->join('channel_member_groups', 'channel_member_groups.channel_id = channels.channel_id AND '.ee()->db->dbprefix('channel_member_groups').'.`group_id` = '.ee()->db->escape($clone), 'left')
                ->get('channels');

            foreach ($query->result() as $row) {
                $_POST[$row->site_id.'_channel_id_'.$row->channel_id] = $row->group_id ? 'y' : 'n';
            }

            $query->free_result();

        } else {

            $query = ee()->db->get('sites');

            $sites = $query->result();

            $query->free_result();

            foreach ($sites as $site) {
                foreach ($this->defaults as $key => $value) {
                    $option = $this->option($key);

                    if (is_null($option)) {
                        $_POST[$site->site_id.'_'.$key] = $value;
                    } else {
                        if ($this->isOptionBool($value) && ! $this->isOptionBool($option)) {
                            throw new \RuntimeException(sprintf('Option %s should be y or n', $key));
                        }

                        $_POST[$site->site_id.'_'.$key] = $option;
                    }
                }

                $query = ee()->db->where('site_id', $site->site_id)
                    ->get('channels');

                foreach ($query->result() as $row) {
                    $_POST[$site->site_id.'_channel_id_'.$row->channel_id] = 'n';
                }

                $query->free_result();

                $query = ee()->db->where('site_id', $site->site_id)
                    ->get('template_groups');

                foreach ($query->result() as $row) {
                    $_POST[$site->site_id.'_template_id_'.$row->group_id] = 'n';
                }

                $query->free_result();
            }

            $query = ee()->db->get('modules');

            foreach ($query->result() as $row) {
                $_POST['module_id_'.$row->module_id] = 'n';
            }

            $query->free_result();
        }

        ee()->load->library('form_validation');

        ee()->update_member_group();

        if (ee()->output->getErrorMessage()) {
            $this->error(ee()->output->getErrorMessage());

            return;
        }

        if (ee()->form_validation->_error_messages) {
            foreach (ee()->form_validation->_error_messages as $error) {
                $this->error($error);
            }

            return;
        }

        $query = ee()->db->select('group_id')
            ->where('group_title', $name)
            ->get('member_groups');

        if ($query->num_rows() === 0) {
            $this->error('Did not create member group.');
            return;
        }

        $groupId = $query->row('group_id');

        $query->free_result();

        $message = sprintf('Member group %s (%s) created.', $name, $groupId);

        $this->info($message);
    }

    protected function isOptionBool($value)
    {
        return $value === 'y' || $value === 'n';
    }
}
