<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

abstract class AbstractCreateFieldFieldpackOptionsCommand extends AbstractCreateFieldCommand
{
    /**
     * {@inheritdoc}
     */
    protected function getFieldtypeOptions()
    {
        return array(
            array(
                'option',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'One or more options. --option="Option Label" --option="option_value : Option Label"',
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getFieldtypeSettings()
    {
        return array(
            $this->getOptionPrefix().'_options' => implode("\n", $this->option('option')),
        );
    }

    protected function getOptionPrefix()
    {
        return $this->getFieldtype();
    }
}
