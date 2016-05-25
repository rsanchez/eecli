<?php

namespace eecli;

use eecli\Command\Contracts\Conditional;
use eecli\Command\Contracts\ExemptFromBootstrap;
use eecli\Command\Contracts\HasRuntimeOptions;
use eecli\Console\GlobalArgvInput;
use eecli\CodeIgniter\ConsoleOutput as CodeIgniterConsoleOutput;
use eecli\CodeIgniter\BootableInterface;
use eecli\CodeIgniter\Cp;
use eecli\CodeIgniter\Functions;
use eecli\Db\ConnectionTester;
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
    const VERSION = '1.0.9';

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
     * A list of Command dirs
     * @var array
     */
    protected $userDefinedCommandDirs = array();

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
    protected static $globalCommands = array();

    /**
     * List of commands that need to be checked if applicable before loading
     * @var array
     */
    protected $conditionalCommands = array();

    /**
     * @var \eecli\Console\GlobalArgvInput
     */
    protected $globalInput;

    /**
     * @var \Symfony\Component\Console\Output\ConsoleOutput
     */
    protected $consoleOutput;

    /**
     * List of errors
     * @var array
     */
    protected $errors = array();

    public function __construct()
    {
        $this->setGlobalInput();

        $this->consoleOutput = new ConsoleOutput();

        if ($this->globalInput->hasParameterOption(array('--ansi'))) {
            $this->consoleOutput->setDecorated(true);
        } elseif ($this->globalInput->hasParameterOption(array('--no-ansi'))) {
            $this->consoleOutput->setDecorated(false);
        }

        parent::__construct(self::NAME, self::VERSION);

        $dispatcher = new EventDispatcher();

        $dispatcher->addListener(ConsoleEvents::COMMAND, array($this, 'onCommand'));

        $this->setDispatcher($dispatcher);

        $this->loadConfig();

        $this->addCoreCommands();

        // start output buffering to intercept show_error calls
        ob_start();

        register_shutdown_function(array($this, 'shutdown'));
    }

    /**
     * Register an error
     * @param string $error
     */
    public function addError($error)
    {
        $this->errors[] = $error;
    }

    /**
     * Look for any errors that EE might have registered
     * and print them.
     *
     * @param  boolean $quit exit if errors are found
     * @return boolean
     */
    public function checkForErrors($quit = false)
    {
        $errors = $this->errors;

        if (isset(ee()->form_validation) && ee()->form_validation->_error_array) {
            foreach (ee()->form_validation->_error_array as $error) {
                $errors[] = $error;
            }
        }

        if ($errors) {
            foreach ($errors as $error) {
                $this->consoleOutput->writeln('<error>'.$error.'</error>');
            }

            $this->errors = array();

            if (isset(ee()->form_validation)) {
                ee()->form_validation->_error_array = array();
            }

            if ($quit) {
                exit;
            }

            return true;
        }

        return false;
    }

    /**
     * Intercept show_error calls
     * @return void
     */
    public function shutdown()
    {
        $output = ob_get_contents();

        ob_end_clean();

        $error = null;

        $defaultMessage = 'Site Error:  Unable to Load Site Preferences; No Preferences Found';

        if (preg_match('/'.preg_quote($defaultMessage).'/', $output)) {
            $error = $defaultMessage;
        } elseif (preg_match('/<div id="error_content">.*?<p>(.*?)<\/p>.*?<\/div>/s', $output, $match)) {
            $error = trim($match[1]);
        } elseif (preg_match('/<h1>error<\/h1>\s*<p>(.*?)<\/p>/s', $output, $match)) {
            $error = trim(strip_tags($match[1]));
        }

        if ($error) {
            if ($error === $defaultMessage) {
                $error = 'eecli could not connect to your database. Please see the doc on troubleshooting: https://github.com/rsanchez/eecli/wiki/Troubleshooting';
            }

            $this->consoleOutput->writeln('<error>'.$error.'</error>');

            //test the db connection
            $tester = ConnectionTester::create(
                ee()->db->dbdriver,
                ee()->db->hostname,
                ee()->db->username,
                ee()->db->password,
                ee()->db->database
            );

            if (! $tester->test()) {
                if ($tester->getError()) {
                    $this->consoleOutput->writeln('');
                    $this->consoleOutput->writeln('<error>'.$tester->getError().'</error>');
                }
            }

            return;
        }

        echo $output;
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

        $this->globalInput = new GlobalArgvInput(null, $inputDefinition);
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

        ee()->benchmark = load_class('Benchmark', 'core');
        ee()->router = load_class('Router', 'core');
        ee()->load->helper('form');
        ee()->load->helper('url');
        ee()->load->library('view');
        ee()->lang->loadfile('cp');
        ee()->load->library('logger');

        if (version_compare(APP_VER, '2.6', '>=')) {
            ee()->view->disable('ee_menu');
        } else {
            ee()->session->userdata['assigned_template_groups'] = array();
        }

        if (version_compare(APP_VER, '2.8', '<') && ! defined('XID_SECURE_HASH')) {
            define('XID_SECURE_HASH', '');
        }

        $query = ee()->db->where('members.group_id', 1)
            ->join('member_groups', 'member_groups.group_id = members.group_id')
            ->limit(1)
            ->get('members');

        // superadmin
        ee()->session->userdata = $query->row_array();
        ee()->session->userdata['group_id'] = '1';
        ee()->session->userdata['assigned_template_groups'] = array();

        $query->free_result();

        ee()->load->library('cp');
        ee()->cp = new Cp(ee()->cp->cp_theme, ee()->cp->cp_theme_url);

        ee()->load->helper('quicktab');
        ee()->cp->set_default_view_variables();
        ee()->load->model('super_model');
    }

    /**
     * Create a new global CI controller instance
     * @param  string         $className
     * @return \CI_Controller
     */
    public function newControllerInstance($className)
    {
        $oldInstance = get_instance();

        if (version_compare(APP_VER, '2.6', '<')) {
            require_once APPPATH.'controllers/ee.php';
        } else {
            require_once APPPATH.'core/EE_Controller.php';
        }

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
        $reflectedProperty->setValue($newInstance);

        // boot the new instance, if necessaory
        if ($newInstance instanceof BootableInterface) {
            $newInstance->boot($this);
        }

        return $newInstance;
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
            ee()->output = new CodeIgniterConsoleOutput($output, $this);
            ee()->functions = new Functions($output, $this);
        }

        if ($this->doesCommandHaveRuntimeOptions($command)) {
            // we use this to allow the command to access cli arguments
            // during the getRuntimeOptions call
            $input = new GlobalArgvInput(null, $event->getCommand()->getDefinition());

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
        if ($this->globalInput->getCommandName() === 'init') {
            return false;
        }

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
        $this->consoleOutput->writeln('<comment>Searching for your system folder...</comment>');

        $startTime = microtime(true);

        $finder = new Finder();

        $finder->files()
            ->in(getcwd())
            ->ignoreUnreadableDirs()
            ->name('CodeIgniter.php');

        $systemPath = null;

        foreach ($finder as $file) {
            $currentTime = microtime(true);

            if (($currentTime - $startTime) > 5) {
                $this->consoleOutput->writeln('<error>Could not automatically find your system folder within 5 seconds. Please create a config file using eecli init and set your system folder manually.</error>');

                exit;
            }

            $path = $file->getRealPath();

            $parentDir = dirname($path);

            $grandparentDir = dirname($parentDir);

            $greatgrandparentDir = dirname($grandparentDir);

            if (basename($parentDir) === 'core' && basename($grandparentDir) === 'system' && basename($greatgrandparentDir) === 'codeigniter') {
                $systemPath = dirname($greatgrandparentDir);

                break;
            }
        }

        if ($systemPath) {
            $this->consoleOutput->writeln('<info>System folder ./'.str_replace(getcwd().DIRECTORY_SEPARATOR, '', $systemPath).' found.</info>');
        } else {
            $this->consoleOutput->writeln('<error>Could not automatically find your system folder. Please create a config file using eecli init and set your system folder manually.</error>');
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

        if (isset($config['commandDirs']) && is_array($config['commandDirs'])) {
            $this->userDefinedCommandDirs = $config['commandDirs'];
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
            ->name('*Command.php');

        foreach ($finder as $file) {
            $class = '\\eecli\\Command\\'.$file->getBasename('.php');

            $reflectionClass = new ReflectionClass($class);

            if (! $reflectionClass->isInstantiable()) {
                continue;
            }

            $command = new $class();

            if ($command instanceof Conditional) {
                $this->conditionalCommands[] = $command;
            } else {
                $this->registerCommand($command);
            }
        }
    }

    /**
     * Add core commands that verify as applicable
     * @return void
     */
    public function addConditionalCommands()
    {
        if (! $this->canBeBootstrapped()) {
            return;
        }

        foreach ($this->conditionalCommands as $command) {
            if ($command->isApplicable()) {
                $this->registerCommand($command);
            }
        }
    }

    /**
     * Find any commands defined in addons
     * and add them to the Application
     * @return void
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
                    $this->registerCommand($command);
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

        foreach ($this->userDefinedCommandDirs as $commandNamespace => $commandDir) {
            foreach ($this->findCommandsInDir($commandDir, $commandNamespace) as $class) {
                $this->registerCommand($class);
            }
        }
    }

    /**
     * Find any globally registered Commands
     * and add them to the Application
     * @return void
     */
    public function addGlobalCommands()
    {
        foreach (self::$globalCommands as $class) {
            $this->registerCommand($class);
        }
    }

    /**
     * Get a list of Symfony Console Commands classes
     * in the specified directory
     *
     * @param  string $dir
     * @param  string $namespace
     * @return array
     */
    public function findCommandsInDir($dir, $namespace = null)
    {
        $commands = array();

        if ($namespace) {
            $namespace = rtrim($namespace, '\\').'\\';
        }

        $finder = new Finder();

        $finder->files()
            ->in($dir)
            ->depth('== 0')
            ->name('*.php');

        foreach ($finder as $file) {
            $class = $namespace.$file->getBasename('.php');

            if (! class_exists($class)) {
                continue;
            }

            $reflectionClass = new ReflectionClass($class);

            if (! $reflectionClass->isInstantiable()) {
                continue;
            }

            $parentClass = null;

            while ($reflectionClass = $reflectionClass->getParentClass()) {
                $parentClass = $reflectionClass->getName();
            }

            if ($parentClass !== 'Symfony\\Component\\Console\\Command\\Command') {
                continue;
            }

            $commands[] = $class;
        }

        return $commands;
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
            $command = call_user_func($class, $this);
        } else {
            $command = new $class();
        }

        // add global options to this command
        foreach ($this->globalInput->getDefinition()->getOptions() as $option) {
            $command->getDefinition()->addOption($option);
        }

        $this->add($command);
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
