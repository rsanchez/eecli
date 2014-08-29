<?php

namespace eecli\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

class ShowConfigCommand extends Command
{
    protected function configure()
    {
        $this->setName('show:config');
        $this->setDescription('Show config items.');

        $this->addArgument(
            'key',
            InputArgument::OPTIONAL,
            'Which config item do you want to show? (Leave blank to show all)'
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $key = $input->getArgument('key');

        if ($key) {

            $value = $this->dump(ee()->config->item($key));

            $output->writeln($value);

            return;
        }

        $table = new Table($output);

        $table->setHeaders(array('Key', 'Value'));

        $config = ee()->config->config;

        ksort($config);

        foreach ($config as $key => $value) {
            if ( ! is_string($value) && ! is_int($value) && ! is_float($value)) {
                $value = $this->dump($value);
            }

            $table->addRow(array($key, $value));
        }

        $table->render();
    }
}