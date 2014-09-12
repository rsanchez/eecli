<?php

namespace eecli\CodeIgniter;

use Symfony\Component\Console\Output\OutputInterface;

class ConsoleOutput extends \EE_Output
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


    public function __construct(OutputInterface $output)
    {
        $this->output = $output;

        // you need to load the template library to override the fatal error
        ee()->load->library('template', null, 'TMPL');
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
     * Suppress any header-setting
     * @param string  $header
     * @param boolean $replace
     */
    public function set_header($header, $replace = true)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function fatal_error($errorMessage)
    {
        $this->resetMessages();

        $this->errorMessage = str_replace('&#171; Back', '', $errorMessage);

        $this->errorMessage = strip_tags($errorMessage);
    }

    /**
     * Get a success message
     * @return string|null
     */
    public function getSuccessMessage()
    {
        return $this->successMessage;
    }

    /**
     * Get an error message
     * @return string|null
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * {@inheritdoc}
     */
    public function send_ajax_response($data)
    {
        $this->resetMessages();

        if (is_scalar($data)) {
            $this->successMessage = $data;
        } elseif (! empty($data['error'])) {
            $this->errorMessage = $data['error'];
        } elseif (! empty($data['message_failure'])) {
            $this->errorMessage = $data['message_failure'];
        } elseif (! empty($data['success'])) {
            $this->successMessage = $data['success'];
        } elseif (! empty($data['message_success'])) {
            $this->successMessage = $data['message_success'];
        } elseif (is_array($data) && is_string(current($data))) {
            $this->successMessage = implode(PHP_EOL, $data);
        } else {
            $this->successMessage = print_r($data, TRUE);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function show_message($data)
    {
        $this->resetMessages();

        if (isset($data['content'])) {
            $this->successMessage = strip_tags($data['content']);
        } else {
            $this->successMessage = '';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function show_user_error($type = null, $errors)
    {
        $this->resetMessages();

        if (! is_array($errors)) {
            $errors = array($errors);
        }

        $this->errorMessage = implode(PHP_EOL, $errors);
    }
}
