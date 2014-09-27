<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CreateMemberCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'create:member';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a member.';

    /**
     * {@inheritdoc}
     */
    protected function getArguments()
    {
        return array(
            array(
                'username_or_email', // name
                InputArgument::REQUIRED, // mode
                'Username or email', // description
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
                'password', // name
                'p', // shortcut
                InputOption::VALUE_OPTIONAL, // mode
                'The password', // description
                null, // default value
            ),
            array(
                'email', // name
                'e', // shortcut
                InputOption::VALUE_OPTIONAL, // mode
                'Email address', // description
                null, // default value
            ),
            array(
                'member_group', // name
                'g', // shortcut
                InputOption::VALUE_OPTIONAL, // mode
                'Member group ID', // description
                null, // default value
            ),
            array(
                'screen_name', // name
                's', // shortcut
                InputOption::VALUE_OPTIONAL, // mode
                'Screen Name', // description
                null, // default value
            ),
            array(
                'hide_password', // name
                'H', // shortcut
                InputOption::VALUE_NONE, // mode
                'Don\'t show the generated password, if applicable', // description
                null, // default value
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        $this->getApplication()->newInstance('\\eecli\\CodeIgniter\\Controller\\MembersController');

        ee()->load->helper(array('string', 'security'));
        ee()->load->library('stats');

        $username = $this->argument('username_or_email');
        $password = $this->option('password') ?: random_string('alnum', 16);
        $screenName = $this->option('screen_name');
        $email = $this->option('email') ?: $username;
        $groupId = $this->option('member_group') ?: ee()->config->item('default_member_group');

        $_POST = array(
            'username' => $username,
            'password' => $password,
            'password_confirm' => $password,
            'screen_name' => $screenName,
            'email' => $email,
            'group_id' => $groupId,
            'bday_y' => '',
            'bday_m' => '',
            'bday_d' => '',
            'url' => 'http://',
            'location' => '',
            'occupation' => '',
            'interests' => '',
            'aol_im' => '',
            'icq' => '',
            'yahoo_im' => '',
            'msn_im' => '',
            'bio' => '',
        );

        ee()->new_member_form();

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

        $query = ee()->db->select('member_id')
            ->where('username', $username)
            ->get('members');

        $memberId = $query->row('member_id');

        $query->free_result();

        $withPassword = $this->option('password') || $this->option('hide_password') ? '' : ' with password '.$password;

        $message = sprintf('Member %s (%s) created%s.', $username, $memberId, $withPassword);

        $this->info($message);
    }
}
