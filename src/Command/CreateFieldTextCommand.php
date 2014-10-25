<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class CreateFieldTextCommand extends AbstractCreateFieldCommand implements HasExamples
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

    public function getExamples()
    {
        return array(
            'Create a Text field in field group 1' => '"Your Field Name" your_field_name 1',
            'Create a Text field of maxlength 255' => '--max_length=255 "Name" name 1',
            'Create a Text field with format xhtml (none, br, or xhtml)' => '--format=xhtml "Name" name 1',
            'Create a Text field with format selectable on the publish page' => '--show_format "Name" name 1',
            'Create a Text field with RTL text direction' => '--text_direction=rtl "Name" name 1',
            'Create a Text field with a content type (all, numeric, integer, or decimal)' => '--content_type=decimal "Name" name 1',
            'Create a Text field with the smileys button' => '--show_smileys "Name" name 1',
            'Create a Text field with the glossary button' => '--show_glossary "Name" name 1',
            'Create a Text field with the spellcheck button' => '--show_spellcheck "Name" name 1',
            'Create a Text field with the file selector button' => '--show_file_selector "Name" name 1',
        );
    }
}
