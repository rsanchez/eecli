<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;
use eecli\Command\Contracts\HasLongDescription;
use Symfony\Component\Console\Input\InputArgument;

class ShowConfigCommand extends Command implements HasExamples, HasLongDescription
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'show:config';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Display a list of config items.';

    /**
     * {@inheritdoc}
     */
    protected function getArguments()
    {
        return array(
            array(
                'key',
                InputArgument::OPTIONAL,
                'Which config item do you want to show? (Leave blank to show all)',
            ),
        );
    }

    protected function dump($value)
    {
        ob_start();

        var_dump($value);

        $value = ob_get_clean();

        // remove trailing newline
        $value = mb_substr($value, 0, -1);

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        $key = $this->argument('key');

        if ($key) {
            $value = $this->dump(ee()->config->item($key));

            $this->line($value);

            return;
        }

        $headers = array('Key', 'Value');

        $rows = array();

        $config = ee()->config->config;

        ksort($config);

        foreach ($config as $key => $value) {
            if (! is_string($value) && ! is_int($value) && ! is_float($value)) {
                $value = $this->dump($value);
            }

            $rows[] = array($key, $value);
        }

        $this->table($headers, $rows);
    }

    /**
     * {@inheritdoc}
     */
    public function getExamples()
    {
        return array(
            'Show all config items' => '',
            'Show the specified config item' => 'site_label',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getLongDescription()
    {
        return <<<EOT
Display a list of EE config items.

![Screenshot of show:config command](https://github.com/rsanchez/eecli/wiki/images/show:config.png)
EOT;
    }
}
