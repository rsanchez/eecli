<?php

namespace eecli\CodeIgniter;

use Symfony\Component\Console\Output\OutputInterface;

class ConsoleOutput extends \EE_Output
{
    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;

        // you need to load the template library to override the fatal error
        ee()->load->library('template', null, 'TMPL');
    }

    /**
     * Suppress any header-setting
     * @param string  $header
     * @param boolean $replace
     */
    public function set_header($header, $replace = true)
    {
    }

    /**
     * Throw a fatal runtime exception
     * @param  string $errorMessage
     * @return void
     */
    public function fatal_error($errorMessage)
    {
        $errorMessage = str_replace('&#171; Back', '', $errorMessage);

        $errorMessage = strip_tags($errorMessage);

        $this->output->writeln('<error>'.$errorMessage.'</error>');

        exit;
    }

    /**
     * Write a message to the console output
     * @param  array $data
     * @return void
     */
    public function show_message($data)
    {
        if (isset($data['content'])) {
            $this->output->writeln('<comment>'.strip_tags($data['content']).'</comment>');
        }

        exit;
    }

    /**
     * Write error message(s) to the console output
     * @param  null         $type
     * @param  string|array $errors
     * @return void
     */
    public function show_user_error($type = null, $errors)
    {
        if (! is_array($errors)) {
            $errors = array($errors);
        }

        foreach ($errors as $errorMessage) {
            $this->output->writeln('<error>'.strip_tags($errorMessage).'</error>');
        }

        exit;
    }
}
