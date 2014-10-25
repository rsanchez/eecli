<?php

namespace eecli\Command\Contracts;

interface HasExamples
{
    /**
     * List of examples
     *   description => arguments
     *
     * @return array
     */
    public function getExamples();
}
