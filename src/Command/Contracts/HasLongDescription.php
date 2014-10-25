<?php

namespace eecli\Command\Contracts;

interface HasLongDescription
{
    /**
     * Get a longer description suitable for documentation
     * @return string
     */
    public function getLongDescription();
}
