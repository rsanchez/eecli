<?php

namespace eecli\eecli\Application;

use eecli\eecli\Application\Config;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Command\ListCommand;
use eecli\eecli\Command\ClearStashCacheCommand;
use eecli\eecli\Command\ClearCeCacheCommand;
use eecli\eecli\Command\ClearEECacheCommand;
use eecli\eecli\Command\GithubAddonInstallerCommand;

class Application extends ConsoleApplication
{
    /**
     * @var \eecli\eecli\Application\Config|null
     */
    protected $config;

    public function __construct(Config $config = null)
    {
        parent::__construct('eecli', '0.0.0-alpha');

        $this->config = $config;

        $this->addUserDefinedCommands();
    }

    /**
     * Find any user-defined Commands in the config
     * and add them
     * @return void
     */
    protected function addUserDefinedCommands()
    {
        if ($this->config) {
            foreach ($this->config->getCommands() as $classname) {
                if (is_callable($classname)) {
                    $this->add(call_user_func($classname));
                } else {
                    $this->add(new $classname);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultCommands()
    {
        return [
            new HelpCommand(),
            new ListCommand(),
            new ClearStashCacheCommand(),
            new ClearCeCacheCommand(),
            new ClearEECacheCommand(),
            new GithubAddonInstallerCommand(),
        ];
    }
}