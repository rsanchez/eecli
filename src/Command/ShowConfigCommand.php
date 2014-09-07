<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class ShowConfigCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'show:config';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Show config items.';

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
            if ( ! is_string($value) && ! is_int($value) && ! is_float($value)) {
                $value = $this->dump($value);
            }

            $rows[] = array($key, $value);
        }

        $this->table($headers, $rows);
    }
}
