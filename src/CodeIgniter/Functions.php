<?php

namespace eecli\CodeIgniter;

use Symfony\Component\Console\Output\OutputInterface;

class Functions extends \EE_Functions
{
    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Suppress redirections and print any messages
     * stored in session flashdata
     *
     * @param  string   $location
     * @param  boolean  $method
     * @param  int|null $statusCode
     * @return void
     */
    public function redirect($location, $method = false, $statusCode = null)
    {
        $success = ee()->session->flashdata(':new:message_success');
        $failure = ee()->session->flashdata(':new:message_failure');

        if ($failure) {
            $this->output->writeln('<error>'.$failure.'</error>');
            exit;
        }

        if ($success) {
            $this->output->writeln('<info>'.$success.'</info>');
            exit;
        }
    }
}
