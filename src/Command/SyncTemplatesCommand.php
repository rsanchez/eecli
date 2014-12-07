<?php

namespace eecli\Command;

use Illuminate\Console\Command;

class SyncTemplatesCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'sync:templates';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Synchronize the template database with your template files.';

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        if (ee()->config->item('save_tmpl_files') !== 'y' || ! ee()->config->item('tmpl_file_basepath')) {
            $this->error('The "Save Templates as Files" system configuration must be turned on to use this command.');

            return;
        }

        $this->getApplication()->newControllerInstance('\\eecli\\CodeIgniter\\Controller\\DesignController');

        ee()->sync_templates();

        $vars = ee()->cp->getVariables();

        $toggle = array();

        foreach ($vars['templates'] as $groupName => $templates) {
            foreach ($templates as $templateName => $templateData) {
                if (isset($templateData['toggle']) && preg_match('#name="toggle\[\]" value="(.*?)"#', $templateData['toggle'], $match)) {
                    $toggle[] = $match[1];
                }
            }
        }

        if (empty($toggle)) {
            $this->error('There are no templates to sync.');

            return;
        }

        $_POST = array(
            'confirm' => 'confirm',
            'toggle' => $toggle,
        );

        ee()->sync_run();

        if (ee()->functions->getErrorMessage()) {
            $this->error(ee()->functions->getErrorMessage());

            return;
        }

        if (ee()->functions->getSuccessMessage()) {
            $this->info(ee()->functions->getSuccessMessage());
        }
    }
}
