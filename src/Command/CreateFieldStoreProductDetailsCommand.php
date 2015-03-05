<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;
use Symfony\Component\Console\Input\InputOption;

class CreateFieldStoreProductDetailsCommand extends AbstractCreateFieldCommand
{
    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a Store: Product Details field.';

    /**
     * {@inheritdoc}
     */
    protected function getFieldtype()
    {
        return 'store';
    }

    /**
     * {@inheritdoc}
     */
    protected function getFieldtypeSettings()
    {
        return array(
            'store' => array(
                'enable_custom_prices' => 0,
                'enable_custom_weights' => 0,
            ),
        );
    }
}
