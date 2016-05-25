<?php

namespace eecli\Console;

use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputDefinition;

/**
 *  A copy of ArgvInput that does not throw errors for unspecified options
 *
 *  Copyright (c) 2004-2014 Fabien Potencier
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:

 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */
class GlobalArgvInput extends Input
{
    private $tokens;
    private $parsed;

    /**
     * Whether to remove options from argv
     * @var bool
     */
    protected $removeOptions = false;

    /**
     * Whether to throw errors for unspecified options or too many arguments
     * @var boolean
     */
    protected $strict = false;

    /**
     * The current position relative to $_SERVER['argv']
     * @var integer
     */
    protected $currentArg = 2;

    /**
     * The name of the command being called
     * @var string
     */
    protected $commandName;

    /**
     * Constructor.
     *
     * @param array           $argv       An array of parameters from the CLI (in the argv format)
     * @param InputDefinition $definition A InputDefinition instance
     *
     * @api
     */
    public function __construct(InputDefinition $definition = null, $removeOptions = false, $strict = false)
    {
        $argv = $_SERVER['argv'];

        // strip the application name
        array_shift($argv);

        // strip the command name
        $this->commandName = array_shift($argv);

        $this->tokens = $argv;

        $this->removeOptions = (bool) $removeOptions;

        $this->strict = (bool) $strict;

        parent::__construct($definition);
    }

    /**
     * Get the command name being called
     * @return string
     */
    public function getCommandName()
    {
        return $this->commandName;
    }

    /**
     * Processes command line arguments.
     */
    protected function parse()
    {
        $parseOptions = true;
        $this->parsed = $this->tokens;
        $this->currentArg = 2;

        while (null !== $token = array_shift($this->parsed)) {
            if ($parseOptions && '' == $token) {
                $this->parseArgument($token);
            } elseif ($parseOptions && '--' == $token) {
                $parseOptions = false;
            } elseif ($parseOptions && 0 === strpos($token, '--')) {
                $this->parseLongOption($token);
            } elseif ($parseOptions && '-' === $token[0] && '-' !== $token) {
                $this->parseShortOption($token);
            } else {
                $this->parseArgument($token);
            }
            $this->currentArg++;
        }
    }

    /**
     * Parses a short option.
     *
     * @param string $token The current token.
     */
    private function parseShortOption($token)
    {
        $name = substr($token, 1);

        if (strlen($name) > 1) {
            if ($this->definition->hasShortcut($name[0]) && $this->definition->getOptionForShortcut($name[0])->acceptValue()) {
                // an option with a value (with no space)
                $this->addShortOption($name[0], substr($name, 1));
            } else {
                $this->parseShortOptionSet($name);
            }
        } else {
            $this->addShortOption($name, null);
        }
    }

    /**
     * Parses a short option set.
     *
     * @param string $name The current token
     *
     * @throws \RuntimeException When option given doesn't exist
     */
    private function parseShortOptionSet($name)
    {
        $len = strlen($name);
        for ($i = 0; $i < $len; $i++) {
            if (!$this->definition->hasShortcut($name[$i])) {
                if (!$this->strict) {
                    continue;
                }

                throw new \RuntimeException(sprintf('The "-%s" option does not exist.', $name[$i]));
            }

            $option = $this->definition->getOptionForShortcut($name[$i]);
            if ($option->acceptValue()) {
                $this->addLongOption($option->getName(), $i === $len - 1 ? null : substr($name, $i + 1));

                break;
            } else {
                $this->addLongOption($option->getName(), null);
            }
        }
    }

    /**
     * Parses a long option.
     *
     * @param string $token The current token
     */
    private function parseLongOption($token)
    {
        $name = substr($token, 2);

        if (false !== $pos = strpos($name, '=')) {
            $this->addLongOption(substr($name, 0, $pos), substr($name, $pos + 1));
        } else {
            $this->addLongOption($name, null);
        }
    }

    /**
     * Parses an argument.
     *
     * @param string $token The current token
     *
     * @throws \RuntimeException When too many arguments are given
     */
    private function parseArgument($token)
    {
        $c = count($this->arguments);

        // if input is expecting another argument, add it
        if ($this->definition->hasArgument($c)) {
            $arg = $this->definition->getArgument($c);
            $this->arguments[$arg->getName()] = $arg->isArray() ? array($token) : $token;

        // if last argument isArray(), append token to last argument
        } elseif ($this->definition->hasArgument($c - 1) && $this->definition->getArgument($c - 1)->isArray()) {
            $arg = $this->definition->getArgument($c - 1);
            $this->arguments[$arg->getName()][] = $token;

        // unexpected argument
        } elseif ($this->strict) {
            throw new \RuntimeException('Too many arguments.');
        }
    }

    /**
     * Adds a short option value.
     *
     * @param string $shortcut The short option key
     * @param mixed  $value    The value for the option
     *
     * @throws \RuntimeException When option given doesn't exist
     */
    private function addShortOption($shortcut, $value)
    {
        if (!$this->definition->hasShortcut($shortcut)) {
            if (!$this->strict) {
                return;
            }

            throw new \RuntimeException(sprintf('The "-%s" option does not exist.', $shortcut));
        }

        $this->addLongOption($this->definition->getOptionForShortcut($shortcut)->getName(), $value);
    }

    /**
     * Adds a long option value.
     *
     * @param string $name  The long option key
     * @param mixed  $value The value for the option
     *
     * @throws \RuntimeException When option given doesn't exist
     */
    private function addLongOption($name, $value)
    {
        if (!$this->definition->hasOption($name)) {
            if (!$this->strict) {
                return;
            }

            throw new \RuntimeException(sprintf('The "--%s" option does not exist.', $name));
        }

        if ($this->removeOptions) {
            unset($_SERVER['argv'][$this->currentArg]);
        }

        $option = $this->definition->getOption($name);

        // Convert false values (from a previous call to substr()) to null
        if (false === $value) {
            $value = null;
        }

        if (null !== $value && !$option->acceptValue()) {
            throw new \RuntimeException(sprintf('The "--%s" option does not accept a value.', $name, $value));
        }

        if (null === $value && $option->acceptValue() && count($this->parsed)) {
            // if option accepts an optional or mandatory argument
            // let's see if there is one provided
            $next = array_shift($this->parsed);
            if (isset($next[0]) && '-' !== $next[0]) {
                $value = $next;
            } elseif (empty($next)) {
                $value = '';
            } else {
                array_unshift($this->parsed, $next);
            }
        }

        if (null === $value) {
            if ($option->isValueRequired()) {
                throw new \RuntimeException(sprintf('The "--%s" option requires a value.', $name));
            }

            if (!$option->isArray()) {
                $value = $option->isValueOptional() ? $option->getDefault() : true;
            }
        }

        if ($option->isArray()) {
            $this->options[$name][] = $value;
        } else {
            $this->options[$name] = $value;
        }
    }

    /**
     * Returns the first argument from the raw parameters (not parsed).
     *
     * @return string The value of the first argument or null otherwise
     */
    public function getFirstArgument()
    {
        foreach ($this->tokens as $token) {
            if ($token && '-' === $token[0]) {
                continue;
            }

            return $token;
        }
    }

    /**
     * Returns true if the raw parameters (not parsed) contain a value.
     *
     * This method is to be used to introspect the input parameters
     * before they have been validated. It must be used carefully.
     *
     * @param string|array $values The value(s) to look for in the raw parameters (can be an array)
     *
     * @return bool true if the value is contained in the raw parameters
     */
    public function hasParameterOption($values, $onlyParams = false)
    {
        $values = (array) $values;

        foreach ($this->tokens as $token) {
            foreach ($values as $value) {
                if ($token === $value || 0 === strpos($token, $value.'=')) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Returns the value of a raw option (not parsed).
     *
     * This method is to be used to introspect the input parameters
     * before they have been validated. It must be used carefully.
     *
     * @param string|array $values  The value(s) to look for in the raw parameters (can be an array)
     * @param mixed        $default The default value to return if no result is found
     *
     * @return mixed The option value
     */
    public function getParameterOption($values, $default = false, $onlyParams = false)
    {
        $values = (array) $values;

        $tokens = $this->tokens;
        while ($token = array_shift($tokens)) {
            foreach ($values as $value) {
                if ($token === $value || 0 === strpos($token, $value.'=')) {
                    if (false !== $pos = strpos($token, '=')) {
                        return substr($token, $pos + 1);
                    }

                    return array_shift($tokens);
                }
            }
        }

        return $default;
    }

    /**
     * Returns a stringified representation of the args passed to the command
     *
     * @return string
     */
    public function __toString()
    {
        $self = $this;
        $tokens = array_map(function ($token) use ($self) {
            if (preg_match('{^(-[^=]+=)(.+)}', $token, $match)) {
                return $match[1].$self->escapeToken($match[2]);
            }

            if ($token && $token[0] !== '-') {
                return $self->escapeToken($token);
            }

            return $token;
        }, $this->tokens);

        return implode(' ', $tokens);
    }
}
