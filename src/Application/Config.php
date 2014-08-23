<?php

namespace eecli\eecli\Application;

class Config
{
    /**
     * Default configuration file name
     */
    const FILENAME = '/.eecli.php';

    /**
     * The path to the system folder
     * @var string|null
     */
    protected $systemPath;

    /**
     * A list of Command objects
     * @var array
     */
    protected $commands = [];

    public function __construct()
    {
        $config = $this->getConfig();

        if (isset($config['system_path'])) {
            $this->systemPath = $config['system_path'];
        }

        if (isset($config['server']) && is_array($config['server'])) {
            $_SERVER = array_merge($_SERVER, $config['server']);
        }

        if (isset($config['commands']) && is_array($config['commands'])) {
            $this->commands = $config['commands'];
        }
    }

    /**
     * Get the path of the system folder
     * @return string|null
     */
    public function getSystemPath()
    {
        return $this->systemPath;
    }

    /**
     * Get additional user-defined Commands.
     * Should either be a class name string or
     * a Closure that returns a Command object.
     *
     * @return array
     */
    public function getCommands()
    {
        return $this->commands;
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
}
