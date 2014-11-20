<?php

namespace eecli\CodeIgniter;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;

class Functions extends \EE_Functions
{
    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * Success message culled from flashdata
     * @var string|null
     */
    protected $successMessage;

    /**
     * Error message culled from flashdata
     * @var string|null
     */
    protected $errorMessage;

    /**
     * Query string variables from redirect
     * @var array
     */
    protected $variables;

    /**
     * Controller instance
     * @var \CI_Controller
     */
    protected $EE;

    /**
     * @var \Symfony\Component\Console\Application
     */
    protected $app;

    public function __construct(OutputInterface $output, Application $app)
    {
        $this->output = $output;

        // for EE 2.5
        $this->EE = get_instance();
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
            $this->app->addError($failure);

            $this->errorMessage = $failure;
        }

        if ($success) {
            $this->successMessage = $success;
        }
    }

    /**
     * Reset errorMessage and successMessage to null
     * @return void
     */
    public function resetMessages()
    {
        $this->errorMessage = null;
        $this->successMessage = null;
    }

    /**
     * Get the flashdata error message
     * @return string|null
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Get the flashdata success message
     * @return string|null
     */
    public function getSuccessMessage()
    {
        return $this->successMessage;
    }

    /**
     * Get the redirect query string variables
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }
}
