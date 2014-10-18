<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class CreateFieldTextareaCommand extends AbstractCreateFieldCommand
{
    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a Textarea field.';

    /**
     * {@inheritdoc}
     */
    protected function getFieldtype()
    {
        return 'textarea';
    }

    /**
     * {@inheritdoc}
     */
    protected function getFieldtypeOptions()
    {
        return array(
            array(
                'rows',
                null,
                InputOption::VALUE_REQUIRED,
                'The number of textarea rows',
                6,
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
            'field_ta_rows' => $this->option('rows'),
            'textarea_field_fmt' => $this->option('format'),
            'textarea_field_show_fmt' => $this->option('show_format') ? 'y' : 'n',
            'textarea_field_text_direction' => $this->option('text_direction'),
            'textarea_field_show_smileys' => $this->option('show_smileys') ? 'y' : 'n',
            'textarea_field_show_glossary' => $this->option('show_glossary') ? 'y' : 'n',
            'textarea_field_show_spellcheck' => $this->option('show_spellcheck') ? 'y' : 'n',
            'textarea_field_show_file_selector' => $this->option('show_file_selector') ? 'y' : 'n',
        );
    }
}
