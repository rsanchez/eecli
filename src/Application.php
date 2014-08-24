<?php

namespace eecli;

use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\ConsoleEvents;

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
    protected $userDefinedCommands = [];

    public function __construct()
    {
        parent::__construct(self::NAME, self::VERSION);

        $dispatcher = new EventDispatcher();

        $dispatcher->addListener(ConsoleEvents::COMMAND, [$this, 'onCommand']);

        $this->setDispatcher($dispatcher);

        $this->loadConfig();

        $this->add(new Command\InitCommand());
        $this->add(new Command\ClearStashCacheCommand());
        $this->add(new Command\ClearCeCacheCommand());
        $this->add(new Command\ClearEECacheCommand());
        $this->add(new Command\GithubAddonInstallerCommand());
    }

    /**
     * List of commands that do no require EE bootstrapping
     * @return array
     */
    protected function getCommandsExemptFromBootstrap()
    {
        return ['help', 'list', 'init'];
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
        $commandName = $command->getName();
        $output = $event->getOutput();

        if (! $this->canBeBootstrapped() && ! in_array($commandName, $this->getCommandsExemptFromBootstrap())) {
            throw new \Exception('Your system path could not be found.');
        }
    }

    /**
     * Whether or not a system folder was found
     * @return bool
     */
    public function canBeBootstrapped()
    {
        return $this->hasValidSystemPath;
    }

    /**
     * Looks for ~/.eecli.php and ./.eecli.php
     * and combines them into an array
     *
     * @return void
     */
    protected function loadConfig()
    {
        $config = [];

        // look for ~/.eecli.php in the user's home directory
        if (isset($_SERVER['HOME']) && file_exists($_SERVER['HOME'].self::FILENAME)) {
            $temp = require $_SERVER['HOME'].self::FILENAME;

            if (is_array($temp)) {
                $config = array_merge($config, $temp);
            }

            unset($temp);
        }

        // look for .eecli.php in the current working directory
        if (file_exists(getcwd().self::FILENAME)) {
            $temp = require getcwd().self::FILENAME;

            if (is_array($temp)) {
                $config = array_merge($config, $temp);
            }

            unset($temp);
        }

        if (isset($config['server']) && is_array($config['server'])) {
            $_SERVER = array_merge($_SERVER, $config['server']);
        }

        if (isset($config['assign_to_config']) && is_array($config['assign_to_config'])) {
            global $assign_to_config;
            $assign_to_config = $config['assign_to_config'];
        }

        $systemPath = isset($config['system_path']) ? $config['system_path'] : 'system';

        if ($this->hasValidSystemPath = is_dir($systemPath)) {
            global $system_path;
            $system_path = $systemPath;
        }

        // Session class needs this
        if (! isset($_SERVER['REMOTE_ADDR'])) {
            $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        }

        if (isset($config['commands']) && is_array($config['commands'])) {
            $this->userDefinedCommands = $config['commands'];
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
                $this->add(new $classname);
            }
        }
    }
}
