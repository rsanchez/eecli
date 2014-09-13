<?php

namespace eecli;

use eecli\Command\ExemptFromBootstrapInterface;
use eecli\CodeIgniter\ConsoleOutput as CodeIgniterConsoleOutput;
use eecli\CodeIgniter\BootableInterface;
use eecli\CodeIgniter\Cp;
use eecli\CodeIgniter\Functions;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\ConsoleEvents;
use Doctrine\Instantiator\Instantiator;
use ReflectionClass;

class Application extends ConsoleApplication
{
    /**
     * Symfony Console Application name
     */
    const NAME = 'eecli';

    /**
     * Symfony Console Application version
     */
    const VERSION = '0.0.0-alpha';

    /**
     * Default configuration file name
     */
    const FILENAME = '/.eecli.php';

    /**
     * Whether the system folder has been set/guessed correctly
     * @var bool
     */
    protected $hasValidSystemPath = false;

    /**
     * A list of Command objects
     * @var array
     */
    protected $userDefinedCommands = array();

    /**
     * A list of callbacks to fire on events
     * @var array
     */
    protected $eventCallbacks = array();

    /**
     * Path to the system folder
     * @var string
     */
    protected $systemPath = 'system';

    /**
     * Author name for generated addons
     * @var string
     */
    protected $addonAuthorName = '';

    /**
     * Author url for generated addons
     * @var string
     */
    protected $addonAuthorUrl = '';

    public function __construct()
    {
        parent::__construct(self::NAME, self::VERSION);

        $dispatcher = new EventDispatcher();

        $dispatcher->addListener(ConsoleEvents::COMMAND, array($this, 'onCommand'));

        $this->setDispatcher($dispatcher);

        $this->loadConfig();

        $this->add(new Command\InitCommand());
        $this->add(new Command\ClearStashCacheCommand());
        $this->add(new Command\ClearCeCacheCommand());
        $this->add(new Command\ClearEECacheCommand());
        $this->add(new Command\GithubAddonInstallerCommand());
        $this->add(new Command\ReplCommand());
        $this->add(new Command\ShowConfigCommand());
        $this->add(new Command\UpdateAddonsCommand());
        $this->add(new Command\CreateChannelCommand());
        $this->add(new Command\CreateTemplateCommand());
        $this->add(new Command\CreateTemplateGroupCommand());
        $this->add(new Command\GenerateCommandCommand());
        $this->add(new Command\GenerateAddonCommand());
        $this->add(new Command\GenerateHtaccessCommand());
        $this->add(new Command\DbDumpCommand());
        $this->add(new Command\DeleteTemplateCommand());
        $this->add(new Command\DeleteTemplateGroupCommand());
        $this->add(new Command\SyncTemplatesCommand());
        $this->add(new Command\ShowTemplatesCommand());
    }

    /**
     * Boot items necessary for a CP controller action
     * @return void
     */
    public function bootCp()
    {
        // constants
        define('CSRF_TOKEN', '0');
        define('PATH_CP_THEME', PATH_THEMES.'cp_themes/');
        define('BASE', SELF.'?S=0&amp;D=cp');

        // superadmin
        ee()->session->userdata['group_id'] = '1';

        ee()->benchmark = load_class('Benchmark', 'core');
        ee()->router = load_class('Router', 'core');
        ee()->load->helper('form');
        ee()->load->helper('url');
        ee()->load->library('view');
        ee()->view->disable('ee_menu');
        ee()->lang->loadfile('cp');
        ee()->load->library('logger');

        ee()->load->library('cp');
        ee()->cp = new Cp(ee()->cp->cp_theme, ee()->cp->cp_theme_url);

        ee()->load->helper('quicktab');
        ee()->cp->set_default_view_variables();
        ee()->load->model('super_model');
    }

    /**
     * Create a new global CI controller instance
     * @param  string $className
     * @return void
     */
    public function newInstance($className)
    {
        $oldInstance = get_instance();

        $instantiator = new Instantiator();

        $newInstance = $instantiator->instantiate($className);

        // copy existing controller props over to new instance
        $controllerProperties = get_object_vars($oldInstance);

        foreach ($controllerProperties as $key => $value) {
            $newInstance->$key = $value;
        }

        // replace the global instance
        $reflectedClass = new ReflectionClass('CI_Controller');
        $reflectedProperty = $reflectedClass->getProperty('instance');
        $reflectedProperty->setAccessible(true);
        $reflectedProperty = $reflectedProperty->setValue($newInstance);

        // boot the new instance, if necessaory
        if ($newInstance instanceof BootableInterface) {
            $newInstance->boot($this);
        }
    }

    /**
     * Check whether a command should be exempt from bootstrapping
     * @param  \Symfony\Component\Console\Command\Command $command
     * @return boolean
     */
    protected function isCommandExemptFromBootstrap(SymfonyCommand $command)
    {
        $commandName = $command->getName();

        if ($commandName === 'help' || $commandName === 'list') {
            return true;
        }

        return $command instanceof ExemptFromBootstrapInterface;
    }

    /**
     * On Command Event Handler
     *
     * Check if the current command requires EE bootstrapping
     * and throw an exception if EE is not bootstrapped
     *
     * @param  ConsoleCommandEvent $event
     * @return void
     */
    public function onCommand(ConsoleCommandEvent $event)
    {
        $command = $event->getCommand();

        $output = $event->getOutput();

        if (! $this->isCommandExemptFromBootstrap($command)) {
            if (! $this->canBeBootstrapped()) {
                throw new \Exception('Your system path could not be found.');
            }

            // override EE classes to print errors/messages to console
            ee()->output = new CodeIgniterConsoleOutput($output);
            ee()->functions = new Functions($output);
        }
    }

    /**
     * Whether or not a valid system folder was found
     * @return bool
     */
    public function canBeBootstrapped()
    {
        return is_dir(rtrim($this->systemPath, '/').'/codeigniter');
    }

    /**
     * Get the environment from ENV PHP constant,
     * defined in config.php, or an environment
     * variable called ENV
     * @return string|null
     */
    public function getEnvironment()
    {
        if (defined('ENV')) {
            return ENV;
        } elseif (getenv('ENV')) {
            return getenv('ENV');
        }

        return null;
    }

    /**
     * Traverse up a directory to find a config file
     *
     * @param  string|null $dir defaults to getcwd if null
     * @return string|null
     */
    protected function findConfigFile($dir = null)
    {
        if (is_null($dir)) {
            $dir = getcwd();
        }

        if ($dir === '/') {
            return null;
        }

        if (file_exists($dir.'/'.self::FILENAME)) {
            return $dir.'/'.self::FILENAME;
        }

        $parentDir = dirname($dir);

        if ($parentDir && is_dir($parentDir)) {
            return $this->findConfigFile($parentDir);
        }

        return null;
    }

    /**
     * Looks for ~/.eecli.php and ./.eecli.php
     * and combines them into an array
     *
     * @return void
     */
    protected function loadConfig()
    {
        // Load configuration file(s)
        $config = array();

        // Look for ~/.eecli.php in the user's home directory
        if (isset($_SERVER['HOME']) && file_exists($_SERVER['HOME'].self::FILENAME)) {
            $temp = require $_SERVER['HOME'].self::FILENAME;

            if (is_array($temp)) {
                $config = array_merge($config, $temp);
            }

            unset($temp);
        }

        $configFile = $this->findConfigFile();

        // Look for the config file in the current working directory
        if ($configFile) {
            $temp = require $configFile;

            if (is_array($temp)) {
                $config = array_merge($config, $temp);
            }

            unset($temp);
        }

        // Spoof $_SERVER variables
        if (isset($config['server']) && is_array($config['server'])) {
            $_SERVER = array_merge($_SERVER, $config['server']);
        }

        // Assign variables to EE config
        if (isset($config['assign_to_config']) && is_array($config['assign_to_config'])) {
            global $assign_to_config;
            $assign_to_config = $config['assign_to_config'];
        }

        // Check the EE system path and set it if valid
        if (isset($config['system_path'])) {
            $this->systemPath = $config['system_path'];

            if ($this->canBeBootstrapped()) {
                global $system_path;
                $system_path = $this->systemPath;
            }
        }

        // Session class needs this
        if (! isset($_SERVER['REMOTE_ADDR'])) {
            $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        }

        // Add user-defined commands from config
        if (isset($config['commands']) && is_array($config['commands'])) {
            $this->userDefinedCommands = $config['commands'];
        }

        // Add event callbacks from the config
        if (isset($config['callbacks']) && is_array($config['callbacks'])) {
            $this->eventCallbacks = $config['callbacks'];
        }

        if (isset($config['addon_author_name'])) {
            $this->addonAuthorName = $config['addon_author_name'];
        }

        if (isset($config['addon_author_url'])) {
            $this->addonAuthorUrl = $config['addon_author_url'];
        }
    }

    /**
     * Get the path to the system folder
     * @return string
     */
    public function getSystemPath()
    {
        return $this->systemPath;
    }

    /**
     * Get the name of the system folder
     * @return string
     */
    public function getSystemFolder()
    {
        return basename($this->systemPath);
    }

    /**
     * Get the default addon author name
     * @return string
     */
    public function getAddonAuthorName()
    {
        return $this->addonAuthorName;
    }

    /**
     * Get the default addon author URL
     * @return string
     */
    public function getAddonAuthorUrl()
    {
        return $this->addonAuthorUrl;
    }

    /**
     * Find any commands defined in addons
     * and add them to the Application
     */
    public function addThirdPartyCommands()
    {
        if (! $this->canBeBootstrapped()) {
            return;
        }

        if (! ee()->extensions->active_hook('eecli_add_commands')) {
            return;
        }

        $commands = array();

        $commands = ee()->extensions->call('eecli_add_commands', $commands, $this);

        if (is_array($commands)) {
            foreach ($commands as $command) {
                if ($command instanceof SymfonyCommand) {
                    $this->add($command);
                }
            }
        }
    }

    /**
     * Find any user-defined Commands in the config
     * and add them to the Application
     * @return void
     */
    public function addUserDefinedCommands()
    {
        foreach ($this->userDefinedCommands as $classname) {
            // is it a callback or a string?
            if (is_callable($classname)) {
                $this->add(call_user_func($classname, $this));
            } else {
                $this->add(new $classname());
            }
        }
    }

    /**
     * Fire an event callback
     * @param  string $event
     * @return void
     */
    public function fire($event)
    {
        if (isset($this->eventCallbacks[$event]) && is_callable($this->eventCallbacks[$event])) {
            call_user_func($this->eventCallbacks[$event], $this);
        }
    }
}
