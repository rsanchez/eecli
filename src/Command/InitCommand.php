<?php

namespace eecli\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class InitCommand extends Command
{
    protected function configure()
    {
        $this->setName('init');
        $this->setDescription('Creates a default configuration file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = getcwd().'/.eecli.php';

        if (file_exists($file)) {
            $helper = $this->getHelper('question');

            $question = new ConfirmationQuestion('A configuration file already exists. Do you want to overwrite? y[n] ', false);

            if (!$helper->ask($input, $output, $question)) {
                return;
            }
        }

        $copy = copy(__DIR__.'/../../sample.eecli.php', $file);

        if ($copy === false) {
            $output->writeln('<error>Could not create the file.</error>');

            return;
        }

        $output->writeln('<info>Configuration file created.</info>');
    }

}