<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;
use eecli\Command\Contracts\HasLongDescription;
use eecli\Command\Contracts\HasOptionExamples;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CreateTemplateCommand extends AbstractCommand implements HasExamples, HasLongDescription, HasOptionExamples
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'create:template';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Create one or more templates.';

    /**
     * {@inheritdoc}
     */
    protected function getArguments()
    {
        return array(
            array(
                'template', // name
                InputArgument::IS_ARRAY | InputArgument::REQUIRED, // mode
                'Template name (ex. site/index site/test)', // description
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getOptions()
    {
        return array(
            array(
                'stdin', // name
                null, // shortcut
                InputOption::VALUE_NONE, // mode
                'Use stdin as template contents.', // description
                null, // default value
            ),
            array(
                'php', // name
                'p', // shortcut
                InputOption::VALUE_NONE, // mode
                'Enable PHP.', // description
                null, // default value
            ),
            array(
                'input', // name
                'i', // shortcut
                InputOption::VALUE_NONE, // mode
                'Parse PHP on input.', // description
                null, // default value
            ),
            array(
                'cache', // name
                'c', // shortcut
                InputOption::VALUE_REQUIRED, // mode
                'Cache for X seconds.', // description
                null, // default value
            ),
            array(
                'protect_js', // name
                'j', // shortcut
                InputOption::VALUE_NONE, // mode
                'Protect javascript', // description
                null, // default value
            ),
            array(
                'notes', // name
                'tn', // shortcut
                InputOption::VALUE_OPTIONAL, // mode
                'Template Notes', // description
                null, // default value
            ),
            array(
                'type', // name
                't', // shortcut
                InputOption::VALUE_REQUIRED, // mode
                'Type', // description
                'webpage', // default value
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        $templates = $this->argument('template');

        $instance = $this->getApplication()->newControllerInstance('\\eecli\\CodeIgniter\\Controller\\DesignController');

        $instance->load->model('template_model');

        $templateData = '';

        if ($this->option('stdin')) {
            $handle = fopen('php://stdin', 'r');

            while (($buffer = fgets($handle, 4096)) !== false) {
                $templateData .= $buffer;
            }
        }

        foreach ($templates as $template) {
            if (! preg_match('#^[a-zA-Z0-9_\-]+/[a-zA-Z0-9_\-]+$#', $template)) {
                $this->error('Template '.$template.' must be in <template_group>/<template_name> format.');

                continue;
            }

            list($groupName, $templateName) = explode('/', $template);

            $query = $instance->db->select('group_id')
                ->where('group_name', $groupName)
                ->get('template_groups');

            // create the group if it doesn't exist
            if ($query->num_rows() === 0) {
                $_POST = array(
                    'group_name' => $groupName,
                    'is_site_default' => 'n',
                    'duplicate_group' => false,
                );

                $instance->new_template_group();

                if ($this->getApplication()->checkForErrors()) {
                    continue;
                }

                $this->comment('Template group '.$groupName.' created.');

                $variables = $instance->functions->getVariables();

                $groupId = $variables['tgpref'];

                // it made an index template, update it if it needs

                if ($instance->config->item('save_tmpl_files') === 'y') {
                    $query = $instance->db->select('template_id')
                        ->where('group_id', $groupId)
                        ->where('template_name', 'index')
                        ->get('templates');

                    if ($query->num_rows() > 0) {
                        $_POST = array(
                            'template_id' => $query->row('template_id'),
                            'template_data' => '',
                            'template_notes' => $this->option('notes'),
                            'save_template_file' => 'y',
                            'save_template_revision' => $instance->config->item('save_tmpl_revisions'),
                        );

                        $instance->update_template();
                    }

                    $query->free_result();
                }
            } else {
                $groupId = $query->row('group_id');
            }

            $query->free_result();

            $templateExists = $instance->db->where('group_id', $groupId)
                ->where('template_name', $templateName)
                ->where('site_id', $instance->config->item('site_id'))
                ->count_all_results('templates') > 0;

            if ($templateExists) {
                $this->error('Template '.$template.' already exists.');

                continue;
            }

            $templateType = $this->option('type');

            $_POST = array(
                'template_name' => $templateName,
                'group_id' => $groupId,
                'template_type' => $templateType,
                'template_notes' => $this->option('notes'),
            );

            $instance->template = $instance->TMPL;

            $instance->create_new_template();

            $variables = $instance->cp->getVariables();

            $templateId = $variables['template_id'];

            $_POST = array(
                'template_id' => $templateId,
                'template_data' => $templateData,
                'template_notes' => $this->option('notes'),
                'save_template_file' => $instance->config->item('save_tmpl_files'),
                'save_template_revision' => $instance->config->item('save_tmpl_revisions'),
            );

            $instance->update_template();

            if ($this->getApplication()->checkForErrors()) {
                continue;
            }

            $_POST = array(
                'template_id' => $templateId,
                'template_name' => $templateName,
                'template_type' => $templateType,
                'template_notes' => $this->option('notes'),
                'cache' => $this->option('cache') ? 'y' : 'n',
                'refresh' => $this->option('cache') ?: 0,
                'allow_php' => $this->option('php') ? 'y' : 'n',
                'protect_javascript' => $this->option('protect_js') ? 'y' : 'n',
                'php_parse_location' => $this->option('input') ? 'i' : 'o',
                'hits' => 0,
            );

            $instance->template_edit_ajax();

            if ($this->getApplication()->checkForErrors()) {
                continue;
            }

            $this->info(sprintf('Template %s (%s) created.', $template, $templateId));
        }
    }

    public function getOptionExamples()
    {
        return array(
            'cache' => '300',
            'type' => 'webpage',
        );
    }

    public function getLongDescription()
    {
        return 'Create a new template. If the template group does not already exist, it will be created.';
    }

    public function getExamples()
    {
        return array(
            'Create a template' => 'site/index',
            'Multiple templates' => 'site/index site/foo',
            'With php enabled' => '--php site/index',
            'With php enabled on input' => '--php --input site/index',
            'With caching on (for 300 seconds)' => '--cache=300 site/index',
            'Protect javascript' => '--protect_js site/index',
            'Set a type: webpage, feed, css, js, static, xml' => '--type=xml site/index',
        );
    }
}
