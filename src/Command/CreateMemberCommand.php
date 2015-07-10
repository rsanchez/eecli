<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;
use eecli\Command\Contracts\HasLongDescription;
use eecli\Command\Contracts\HasOptionExamples;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CreateMemberCommand extends AbstractCommand implements HasExamples, HasOptionExamples, HasLongDescription
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
                InputOption::VALUE_REQUIRED, // mode
                'The password', // description
            ),
            array(
                'email', // name
                'e', // shortcut
                InputOption::VALUE_REQUIRED, // mode
                'Email address', // description
            ),
            array(
                'member_group', // name
                'g', // shortcut
                InputOption::VALUE_REQUIRED, // mode
                'Member group ID', // description
            ),
            array(
                'screen_name', // name
                's', // shortcut
                InputOption::VALUE_REQUIRED, // mode
                'Screen Name', // description
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
        $instance = $this->getApplication()->newControllerInstance('\\eecli\\CodeIgniter\\Controller\\MembersController');

        $instance->load->helper(array('string', 'security'));
        $instance->load->library('stats');

        $username = $this->argument('username_or_email');
        $password = $this->option('password') ?: random_string('alnum', 16);
        $screenName = $this->option('screen_name');
        $email = $this->option('email') ?: $username;
        $groupId = $this->option('member_group') ?: $instance->config->item('default_member_group');

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

        $instance->new_member_form();

        $this->getApplication()->checkForErrors(true);

        $query = $instance->db->select('member_id')
            ->where('username', $username)
            ->get('members');

        $memberId = $query->row('member_id');

        $query->free_result();

        $withPassword = $this->option('password') || $this->option('hide_password') ? '' : ' with password '.$password;

        $message = sprintf('Member %s (%s) created%s.', $username, $memberId, $withPassword);

        $this->info($message);
    }

    public function getOptionExamples()
    {
        return array(
            'password' => '997fa90c393a',
            'email' => 'you@yoursite.com',
            'member_group' => '1',
            'screen_name' => 'Your Name',
        );
    }

    public function getLongDescription()
    {
        return 'Create a new member. If you omit a password, one will be generated for you. If you omit an email, the username will be used as the email address. If you omit a member group, the default member group for your system will be used.';
    }

    public function getExamples()
    {
        return array(
            'Create a member with same username & email' => 'your.email@site.com',
            'Create a member with different username & email' => '--email="your.email@site.com" your_username',
            'Create a member with the specified screen name' => '--screen_name="Your Name" your.email@site.com',
            'Create a member with the specified password' => '--password="so48jf48jss4sk" your.email@site.com',
            'Create a superadmin' => '--member_group=1 your.email@site.com',
        );
    }
}
