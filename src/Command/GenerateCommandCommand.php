<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Handlebars\Handlebars;
use Handlebars\Loader\FilesystemLoader;

class GenerateCommandCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'generate:command';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Generate a custom command.';

    /**
     * {@inheritdoc}
     */
    protected function getOptions()
    {
        return array(
            array(
                'description',
                '',
                InputOption::VALUE_OPTIONAL,
                'The command description.',
            ),
            array(
                'namespace',
                null,
                InputOption::VALUE_OPTIONAL,
                'Add a namespace to the class.',
            ),
            array(
                'has-arguments',
                null,
                InputOption::VALUE_NONE,
                'Whether the command has arguments.',
            ),
            array(
                'has-options',
                null,
                InputOption::VALUE_NONE,
                'Whether the command has options.',
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getArguments()
    {
        return array(
            array(
                'commandName',
                InputArgument::REQUIRED,
                'The name of the command. (ex. show:config)',
            ),
            array(
                'path',
                InputArgument::OPTIONAL,
                'Where to create the Command file.',
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        $commandName = $this->argument('commandName');
        $commandDescription = $this->option('description');
        $hasArguments = $this->option('has-arguments');
        $hasOptions = $this->option('has-options');
        $namespace = $this->option('namespace');

        // where to create the file, default to current directory
        $path = $this->argument('path') ?: '.';

        // make sure it has a trailing slash
        $path = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        // split command into individual words
        $words = preg_split('/[:-_]/', $commandName);

        // camel case
        $words = array_map(function ($word) {
            return mb_strtoupper(mb_substr($word, 0, 1)).mb_substr($word, 1);
        }, $words);

        $className = implode('', $words);

        $handlebars = new Handlebars(array(
            'loader' => new FilesystemLoader(__DIR__.'/../templates/'),
        ));

        $destination = $path.$className.'Command.php';

        $handle = fopen($destination, 'w');

        $output = $handlebars->render('Command.php', array(
            'className' => $className,
            'commandName' => $commandName,
            'commandDescription' => $commandDescription,
            'hasArguments' => $hasArguments,
            'hasOptions' => $hasOptions,
            'namespace' => $namespace,
        ));

        fwrite($handle, $output);

        fclose($handle);

        $this->info($destination.' created.');
    }
}
