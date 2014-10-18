<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class CreateFieldTextCommand extends AbstractCreateFieldCommand
{
    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a Text field.';

    /**
     * {@inheritdoc}
     */
    protected function getFieldtype()
    {
        return 'text';
    }

    /**
     * {@inheritdoc}
     */
    protected function getFieldtypeOptions()
    {
        return array(
            array(
                'maxlength',
                null,
                InputOption::VALUE_REQUIRED,
                'How long should the maxlength be?',
                128,
            ),
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
                'text_direction',
                null,
                InputOption::VALUE_REQUIRED,
                'ltr or rtl',
                'ltr',
            ),
            array(
                'content_type',
                null,
                InputOption::VALUE_REQUIRED,
                'all, numeric, integer, or decimal',
            ),
            array(
                'show_smileys',
                null,
                InputOption::VALUE_NONE,
                'Show smileys?',
            ),
            array(
                'show_glossary',
                null,
                InputOption::VALUE_NONE,
                'Show glossary?',
            ),
            array(
                'show_spellcheck',
                null,
                InputOption::VALUE_NONE,
                'Show spellcheck?',
            ),
            array(
                'show_file_selector',
                null,
                InputOption::VALUE_NONE,
                'Show file selector?',
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getFieldtypeSettings()
    {
        return array(
            'field_maxl' => $this->option('maxlength'),
            'text_field_fmt' => $this->option('format'),
            'text_field_show_fmt' => $this->option('show_format') ? 'y' : 'n',
            'text_field_text_direction' => $this->option('text_direction'),
            'text_field_content_type' => $this->option('content_type'),
            'text_field_show_smileys' => $this->option('show_smileys') ? 'y' : 'n',
            'text_field_show_glossary' => $this->option('show_glossary') ? 'y' : 'n',
            'text_field_show_spellcheck' => $this->option('show_spellcheck') ? 'y' : 'n',
            'text_field_show_file_selector' => $this->option('show_file_selector') ? 'y' : 'n',
        );
    }
}
