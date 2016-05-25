<?php

namespace eecli\Console;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputDefinition;
use RuntimeException;

class GlobalArgvInput extends ArgvInput
{
    /**
     * Original state of tokens given to constructor
     * @var array
     */
    protected $originalTokens;

    /**
     * The name of the command being executed
     * @var string
     */
    protected $commandName;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $argv = null, InputDefinition $definition = null)
    {
        if (null === $argv) {
            $argv = $_SERVER['argv'];
        }

        $tokens = $argv;

        // strip the application name
        array_shift($tokens);

        reset($tokens);

        $this->commandName = current($tokens);

        $this->originalTokens = $tokens;

        parent::__construct($argv, $definition);
    }

    /**
     * Get the command name
     * @return string
     */
    public function getCommandName()
    {
        return $this->commandName;
    }

    /**
     * Get the input definition
     * @return \Symfony\Component\Console\Input\InputDefinition
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * {@inheritdoc}
     */
    protected function parse()
    {
        $parsed = $tokens = $this->originalTokens;

        while (null !== $token = array_shift($parsed)) {
            $this->setTokens(array($token));
            try {
                parent::parse();
            } catch (RuntimeException $e) {
                // ignore these errors, otherwise re-throw it
                if (! preg_match('/^Too many arguments\.$|does not exist\.$/', $e->getMessage())) {
                    throw $e;
                }
            }
        }

        $this->setTokens($tokens);
    }
}
