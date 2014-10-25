<?php

namespace eecli\Command\Contracts;

interface HasOptionExamples
{
    /**
     * List of option examples
     *   option_name => option value
     *
     * @return array
     */
    public function getOptionExamples();
}
