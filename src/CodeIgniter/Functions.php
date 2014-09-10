<?php

namespace eecli\CodeIgniter;

class Functions extends \EE_Functions
{
    /**
     * Suppress redirections and print any messages
     * stored in session flashdata
     *
     * @param  string   $location
     * @param  boolean  $method
     * @param  int|null $statusCode
     * @return [void
     */
    public function redirect($location, $method = false, $statusCode = null)
    {
        $success = ee()->session->flashdata(':new:message_success');
        $failure = ee()->session->flashdata(':new:message_failure');

        if ($failure) {
            show_error($failure);
            exit;
        }

        if ($success) {
            ee()->output->getOutput()->writeln('<info>'.$success.'</info>');
            exit;
        }
    }
}
