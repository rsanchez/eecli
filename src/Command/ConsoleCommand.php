<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Boris\Boris;
use eecli\Command\Contracts\HasLongDescription;

class ConsoleCommand extends AbstractCommand implements HasLongDescription
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'console';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Start an interactive console.';

    public function __construct()
    {
        parent::__construct();

        $this->setAliases(array(
            'repl',
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        $requiredExtensions = array('readline', 'posix', 'pcntl');

        foreach ($requiredExtensions as $extension) {
            if (! extension_loaded($extension)) {
                throw new \Exception(sprintf('PHP %s extension is required for this command.', $extension));
            }
        }

        $boris = new Boris('> ');

        $boris->start();
    }

    /**
     * {@inheritdoc}
     */
    public function getLongDescription()
    {
        return <<<EOT
Start an interactive console.

![Screencast of interactive console](https://github.com/rsanchez/eecli/wiki/images/console.gif)
EOT;
    }
}
