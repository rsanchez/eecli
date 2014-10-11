<?php

namespace eecli;

use eecli\Command\Contracts\ExemptFromBootstrap;
use eecli\Command\Contracts\HasRuntimeOptions;
use eecli\Console\GlobalArgvInput;
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
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
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

    /**
     * List of command classes that should be added to this application
     * @var array
     */
    protected static $globalCommands = [];

    /**
     * @var \eecli\Console\GlobalArgvInput
     */
    protected $globalInput;

    public function __construct()
    {
        $this->setGlobalInput();

        parent::__construct(self::NAME, self::VERSION);

        $dispatcher = new EventDispatcher();

        $dispatcher->addListener(ConsoleEvents::COMMAND, array($this, 'onCommand'));

        $this->setDispatcher($dispatcher);

        $this->loadConfig();

        $this->addCoreCommands();
    }

    protected function setGlobalInput()
    {
        $inputDefinition = new InputDefinition();

        $inputDefinition->addOptions(array(
            new InputOption(
                'system_path', // name
                null, // shortcut
                InputOption::VALUE_OPTIONAL, // mode
                'The path to your system folder', // description
                null // default value
            ),
            new InputOption(
                'http_host', // name
                null, // shortcut
                InputOption::VALUE_OPTIONAL, // mode
                'The HTTP_HOST to spoof in $_SERVER', // description
                null // default value
            ),
            new InputOption(
                'document_root', // name
                null, // shortcut
                InputOption::VALUE_OPTIONAL, // mode
                'The DOCUMENT_ROOT to spoof in $_SERVER', // description
                null // default value
            ),
            new InputOption(
                'request_uri', // name
                null, // shortcut
                InputOption::VALUE_OPTIONAL, // mode
                'The REQUEST_URI to spoof in $_SERVER', // description
                null // default value
            ),
            new InputOption(
                'remote_addr', // name
                null, // shortcut
                InputOption::VALUE_OPTIONAL, // mode
                'The REMOTE_ADDR to spoof in $_SERVER', // description
                null // default value
            ),
            new InputOption(
                'user_agent', // name
                null, // shortcut
                InputOption::VALUE_OPTIONAL, // mode
                'The HTTP_USER_AGENT to spoof in $_SERVER', // description
                null // default value
            ),
        ));

        $this->globalInput = new GlobalArgvInput(null, true);

        $this->globalInput->bind($inputDefinition);
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

        return $command instanceof ExemptFromBootstrap;
    }

    /**
     * Check whether a command has runtime options that need to be loaded
     * @param  \Symfony\Component\Console\Command\Command $command
     * @return boolean
     */
    protected function doesCommandHaveRuntimeOptions(SymfonyCommand $command)
    {
        $commandName = $command->getName();

        if ($commandName === 'help' || $commandName === 'list') {
            return false;
        }

        return $command instanceof HasRuntimeOptions;
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

        if ($this->doesCommandHaveRuntimeOptions($command)) {
            // we use this to allow the command to access cli arguments
            // during the getRuntimeOptions call
            $input = new GlobalArgvInput($event->getCommand()->getDefinition());

            foreach ($command->getRuntimeOptions($this, $input) as $option) {
                $this->getDefinition()->addOption($option);
            }
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
     * Recursively search for an EE system path in the
     * current working directory.
     *
     * @return string|null
     */
    public function findSystemPath()
    {
        $finder = new Finder();

        $finder->files()
            ->in(getcwd())
            ->name('CodeIgniter.php');

        $systemPath = null;

        foreach ($finder as $file) {
            $path = $file->getRealPath();

            $parentDir = dirname($path);

            $grandparentDir = dirname($parentDir);

            $greatgrandparentDir = dirname($grandparentDir);

            if (basename($parentDir) === 'core' && basename($grandparentDir) === 'system' && basename($greatgrandparentDir) === 'codeigniter') {
                $systemPath = dirname($greatgrandparentDir);

                break;
            }
        }

        return $systemPath;
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

        if ($this->globalInput->getOption('system_path')) {
            $config['system_path'] = $this->globalInput->getOption('system_path');
        }

        if (empty($config['system_path'])) {
            // try to find the system path
            $config['system_path'] = $this->findSystemPath();
        }

        // Spoof $_SERVER variables
        if (isset($config['server']) && is_array($config['server'])) {
            $_SERVER = array_merge($_SERVER, $config['server']);
        }

        if ($this->globalInput->getOption('http_host')) {
            $_SERVER['HTTP_HOST'] = $this->globalInput->getOption('http_host');
        }

        if ($this->globalInput->getOption('document_root')) {
            $_SERVER['DOCUMENT_ROOT'] = $this->globalInput->getOption('document_root');
        }

        if ($this->globalInput->getOption('request_uri')) {
            $_SERVER['REQUEST_URI'] = $this->globalInput->getOption('request_uri');
        }

        if ($this->globalInput->getOption('remote_addr')) {
            $_SERVER['REMOTE_ADDR'] = $this->globalInput->getOption('remote_addr');
        }

        if ($this->globalInput->getOption('user_agent')) {
            $_SERVER['HTTP_USER_AGENT'] = $this->globalInput->getOption('user_agent');
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
     * Add all the core commands to the application
     * @return void
     */
    public function addCoreCommands()
    {
        $finder = new Finder();

        $finder->files()
            ->in(__DIR__.'/Command')
            ->depth('== 0')
            ->name('*.php');

        foreach ($finder as $file) {
            $class = '\\eecli\\Command\\'.$file->getBasename('.php');

            $this->add(new $class());
        }
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
        foreach ($this->userDefinedCommands as $class) {
            $this->registerCommand($class);
        }
    }

    /**
     * Find any globally registered Commands
     * and add them to the Application
     */
    public function addGlobalCommands()
    {
        foreach (self::$globalCommands as $class) {
            $this->registerCommand($class);
        }
    }

    /**
     * Register a Command class globally
     * @param  string $class
     * @return void
     */
    public static function registerGlobalCommand($class)
    {
        array_push(self::$globalCommands, $class);
    }

    /**
     * Add a command to the Application by class name
     * or callback that return a Command class
     * @param  string|callable $class class name or callback that returns a command
     * @return void
     */
    public function registerCommand($class)
    {
        // is it a callback or a string?
        if (is_callable($class)) {
            $this->add(call_user_func($class, $this));
        } else {
            $this->add(new $class());
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
