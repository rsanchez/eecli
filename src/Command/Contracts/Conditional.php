<?php

namespace eecli\Command\Contracts;

/**
 * A command that needs checked if applicable to the current installation
 */
interface Conditional
{
    /**
     * Check if the current comment is applicable to the current installation
     * ex. Check if a particular addon is installed
     *
     * @return boolean
     */
    public function isApplicable();
}
