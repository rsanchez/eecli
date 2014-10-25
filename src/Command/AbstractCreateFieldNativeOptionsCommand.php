<?php

namespace eecli\Command;

use Symfony\Component\Console\Input\InputOption;

abstract class AbstractCreateFieldNativeOptionsCommand extends AbstractCreateFieldCommand
{
    /**
     * {@inheritdoc}
     */
    protected function getFieldtypeOptions()
    {
        return array(
            array(
                'format',
                null,
                InputOption::VALUE_REQUIRED,
                'none, br, or xhtml',
                'none',
            ),
            array(
                'show_format',
                null,
                InputOption::VALUE_NONE,
                'Show formatting dropdown on publish page',
            ),
            array(
                'option',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'One or more options',
            ),
            array(
                'pre_populate',
                null,
                InputOption::VALUE_REQUIRED,
                'Pre-populate the dropdown with values from this channel field ID',
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getFieldtypeSettings()
    {
        return array(
            $this->getFieldtype().'_field_fmt' => $this->option('format'),
            $this->getFieldtype().'_field_show_fmt' => $this->option('show_format') ? 'y' : 'n',
            $this->getFieldtype().'_field_list_items' => implode("\n", $this->option('option')),
            $this->getFieldtype().'_field_pre_populate' => $this->option('pre_populate') ? 'y' : 'n',
            $this->getFieldtype().'_field_pre_populate_id' => $this->option('pre_populate'),
        );
    }

    public function getFieldtypeOptionExamples()
    {
        return array(
            'option' => 'Foo',
            'pre_populate' => '1',
        );
    }
}
