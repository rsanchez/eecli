<?php

namespace eecli\CodeIgniter;

use Symfony\Component\Console\Output\OutputInterface;

class Functions extends \EE_Functions
{
    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    protected $successMessage;
    protected $errorMessage;
    protected $variables;

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
        $this->successMessage = null;
        $this->errorMessage = null;

        parse_str(parse_url($location, PHP_URL_QUERY), $this->variables);

        $success = ee()->session->flashdata(':new:message_success');
        $failure = ee()->session->flashdata(':new:message_failure');

        if ($failure) {
            $this->errorMessage = $failure;
        }

        if ($success) {
            $this->successMessage = $success;
        }
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function getSuccessMessage()
    {
        return $this->successMessage;
    }

    public function getVariables()
    {
        return $this->variables;
    }
}
