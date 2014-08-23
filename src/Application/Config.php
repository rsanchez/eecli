<?php

namespace eecli\Application;

use Symfony\Component\Console\Application;

class Config
{
    /**
     * Default configuration file name
     */
    const FILENAME = '/.eecli.php';

    /**
     * The path to the system folder
     * @var string
     */
    protected $systemPath = "system";

    /**
     * A list of Command objects
     * @var array
     */
    protected $commands = [];

    public function __construct()
    {
        global $assign_to_config;

        $config = $this->getConfig();

        if (isset($config['system_path'])) {
            $this->systemPath = $config['system_path'];
        }

        if (isset($config['server']) && is_array($config['server'])) {
            $_SERVER = array_merge($_SERVER, $config['server']);
        }

        if (isset($config['assign_to_config']) && is_array($config['assign_to_config'])) {
            $assign_to_config = array_merge($assign_to_config, $config['assign_to_config']);
        }

        // Session class needs this
        if (! isset($_SERVER['REMOTE_ADDR'])) {
            $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        }

        if (isset($config['commands']) && is_array($config['commands'])) {
            $this->commands = $config['commands'];
        }
    }

    /**
     * Get the path of the system folder
     * @return string
     */
    public function getSystemPath()
    {
        return $this->systemPath;
    }

    /**
     * Looks for ~/.eecli.php and ./.eecli.php
     * and combines them into an array
     *
     * @return array
     */
    protected function getConfig()
    {
        $config = [];

        if (isset($_SERVER['HOME']) && file_exists($_SERVER['HOME'].self::FILENAME)) {
            $temp = require $_SERVER['HOME'].self::FILENAME;

            if (is_array($temp)) {
                $config = array_merge($config, $temp);
            }
        }

        if (file_exists(getcwd().self::FILENAME)) {
            $temp = require getcwd().self::FILENAME;

            if (is_array($temp)) {
                $config = array_merge($config, $temp);
            }
        }

        return $config;
    }

    /**
     * Find any user-defined Commands in the config
     * and add them
     * @return void
     */
    public function addUserDefinedCommands(Application $app)
    {
        foreach ($this->commands as $classname) {
            // is it a callback or a string?
            if (is_callable($classname)) {
                $app->add(call_user_func($classname));
            } else {
                $app->add(new $classname);
            }
        }
    }
}
