<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;
use Symfony\Component\Console\Input\InputOption;

class CreateFieldWygwamCommand extends AbstractCreateFieldCommand implements HasExamples
{
    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a Wygwam field.';

    /**
     * {@inheritdoc}
     */
    protected function getFieldtype()
    {
        return 'wygwam';
    }

    /**
     * {@inheritdoc}
     */
    protected function getFieldtypeOptions()
    {
        return array(
            array(
                'config',
                null,
                InputOption::VALUE_REQUIRED,
                'ID of Wygwam configuration',
            ),
            array(
                'defer',
                null,
                InputOption::VALUE_NONE,
                'Defer initialization?',
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getFieldtypeSettings()
    {
        $config = $this->option('config');

        if (! $config) {
            $query = ee()->db->select('config_id')
                ->order_by('config_id', 'asc')
                ->limit(1)
                ->get('wygwam_configs');

            $config = $query->row('config_id');

            $query->free_result();

            if (! $config) {
                throw new \RuntimeException('You must first create a Wygwam configuration.');
            }
        }

        return array(
            'wygwam' => array(
                'config' => $config,
                'defer' => $this->option('defer') ? 'y' : 'n',
            ),
        );
    }

    protected function getFieldtypeOptionExamples()
    {
        return array(
            'config' => '1',
        );
    }

    public function getExamples()
    {
        return array(
            'Create a Wygwam field in field group 1' => '"Your Field Name" your_field_name 1',
            'Create a Wygwam field with Wygwam configuration 1' => '--config=1 "Name" name 1',
            'Create a Wygwam field with deferred initialization' => '--defer "Name" name 1',
        );
    }
}
