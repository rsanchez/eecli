<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class CreateFieldTextareaCommand extends AbstractCreateFieldCommand implements HasExamples
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

    public function getExamples()
    {
        return array(
            'Create a Textarea field in field group 1' => '"Your Field Name" your_field_name 1',
            'Create a Textarea field with 10 rows' => '--rows=10 "Name" name 1',
            'Create a Textarea field with format xhtml (none, br, or xhtml)' => '--format=xhtml "Name" name 1',
            'Create a Textarea field with format selectable on the publish page' => '--show_format "Name" name 1',
            'Create a Textarea field with RTL text direction' => '--text_direction=rtl "Name" name 1',
            'Create a Textarea field with the smileys button' => '--show_smileys "Name" name 1',
            'Create a Textarea field with the glossary button' => '--show_glossary "Name" name 1',
            'Create a Textarea field with the spellcheck button' => '--show_spellcheck "Name" name 1',
            'Create a Textarea field with the file selector button' => '--show_file_selector "Name" name 1',
        );
    }
}
