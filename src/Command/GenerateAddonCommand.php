<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Question\Question;
use Handlebars\Handlebars;
use Handlebars\Loader\FilesystemLoader;

class GenerateAddonCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'generate:addon';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Generate an addon.';

    /**
     * Template variables
     * @var array
     */
    protected $vars = array();

    /**
     * Handlebars renderer
     * @var \Handlebars\Handlebars
     */
    protected $handlebars;

    protected function setQuestionRequired(Question $question, $errorMessage = 'This field is required.')
    {
        $question->setValidator(function ($answer) use ($errorMessage) {
            if (strlen($answer) === 0) {
                throw new \RuntimeException($errorMessage);
            }

            return $answer;
        });
    }

    /**
     * Confirm a question with the user.
     *
     * @param  string $question
     * @param  bool   $default
     * @return bool
     */
    public function confirm($question, $default = true)
    {
        $question .= $default ? ' [Yn]' : ' [yN]';

        return parent::confirm($question, $default);
    }

    /**
     * Prompt the user for input.
     *
     * @param  string $question
     * @param  string $default
     * @return string
     */
    public function ask($question, $default = null)
    {
        $helper = $this->getHelperSet()->get('question');

        $defaultMessage = is_null($default) ? '' : " (default: {$default})";

        $question = new Question("<question>{$question}{$defaultMessage}</question> ", $default);

        return $helper->ask($this->input, $this->output, $question);
    }

    /**
     * Prompt the user for input.
     *
     * @param  string $question
     * @param  string $default
     * @param  string $errorMessage
     * @return string
     */
    public function askRequired($question, $default = null, $errorMessage = 'This field is required.')
    {
        $helper = $this->getHelperSet()->get('question');

        $defaultMessage = is_null($default) ? '' : " (default: {$default})";

        $question = new Question("<question>{$question}{$defaultMessage}</question> ", $default);

        $this->setQuestionRequired($question, $errorMessage);

        return $helper->ask($this->input, $this->output, $question);
    }

    protected function askForHook(&$extensionHooks)
    {
        $hasHooks = count($extensionHooks) > 0;

        if ($hasHooks) {
            $extensionHook = $this->ask('Add another extension hook (Leave blank to complete generator):');
        } else {
            $extensionHook = $this->ask('Which extension hook does this extension use?', 'channel_entries_query', 'You must choose an extension hook.');
        }

        if ($extensionHook) {
            $extensionMethod = $this->ask('What method name does the prior hook use?', $extensionHook) ?: $extensionHook;

            $extensionHooks[] = array(
                'hook' => $extensionHook,
                'method' => $extensionMethod,
            );

            $this->askForHook($extensionHooks);
        }
    }

    protected function mkdir($pathname)
    {
        if (! is_dir($pathname)) {
            if (! mkdir($pathname)) {
                throw new \RuntimeException('Could not create directory '.$pathname);
            }
        }
    }

    protected function template($template, $destination)
    {
        $handle = fopen($destination, 'w');

        $output = $this->handlebars->render($template, $this->vars);

        fwrite($handle, $output);

        fclose($handle);

        $this->comment(basename($destination).' created.');
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        $this->handlebars = new Handlebars(array(
            'loader' => new FilesystemLoader(__DIR__.'/../templates/addon/'),
        ));

        $systemPath = PATH_THIRD;
        $themePath = PATH_THIRD_THEMES;

        $gitUser = trim(shell_exec('git config --get github.user'));
        $defaultAuthorName = $this->getApplication()->getAddonAuthorName() ?: $gitUser;
        $defaultAuthorUrl = $this->getApplication()->getAddonAuthorUrl();

        if (! $defaultAuthorUrl && $gitUser) {
            $defaultAuthorUrl = 'https://github.com/'.$gitUser;
        }

        $addonTypes = $this->choice(
            'Which add-on type(s) are you making? (default: plugin)',
            array(
                'plugin',
                'extension',
                'module',
                'fieldtype',
                'accessory',
                'plugin + extension',
                'plugin + extension + fieldtype',
                'plugin + extension + fieldtype + accessory',
                'plugin + fieldtype',
                'plugin + fieldtype + accessory',
                'plugin + accessory',
                'extension + fieldtype',
                'extension + fieldtype + accessory',
                'extension + accessory',
                'module + extension',
                'module + extension + fieldtype',
                'module + extension + fieldtype + accessory',
                'module + fieldtype',
                'module + fieldtype + accessory',
                'module + accessory',
            ),
            '0',
            null,
            true
        );

        $this->vars['addonTypes'] = array();

        foreach ($addonTypes as $type) {
            foreach (explode(' + ', $type) as $type) {
                $this->vars['addonTypes'][] = $type;
            }
        }

        $this->vars['addonTypes'] = array_unique($this->vars['addonTypes']);

        $this->vars['hasPlugin'] = in_array('plugin', $this->vars['addonTypes']);
        $this->vars['hasExtension'] = in_array('extension', $this->vars['addonTypes']);
        $this->vars['hasModule'] = in_array('module', $this->vars['addonTypes']);
        $this->vars['hasFieldtype'] = in_array('fieldtype', $this->vars['addonTypes']);
        $this->vars['hasAccessory'] = in_array('accessory', $this->vars['addonTypes']);

        $this->vars['addonName'] = $this->askRequired('What do you want to name your add-on? ex. Google Maps', null, 'You must choose an add-on name.');

        $defaultAddonSlug = preg_replace('/[^a-z0-9_]/', '', preg_replace('/\s+/', '_', strtolower($this->vars['addonName'])));

        $this->vars['addonSlug'] = $this->askRequired("What slug name do you want to give your add-on?", $defaultAddonSlug, 'You must choose an add-on slug.');

        $this->vars['addonDescription'] = $this->vars['hasPlugin'] || $this->vars['hasExtension'] || $this->vars['hasModule'] || $this->vars['hasAccessory'] ? $this->ask('What is the description for this add-on?') : '';

        $this->vars['authorName'] = $this->ask('What is your name?', $defaultAuthorName);

        $this->vars['authorUrl'] = $this->ask('What is your URL?', $defaultAuthorUrl);

        $this->vars['hasTheme'] = $this->confirm('Does this add-on need theme files?', false);

        /*
        $systemPath = $this->ask('What is the system path?', $systemPath);

        $themePath = $this->vars['hasTheme'] ? $this->ask('What is the theme path?', $themePath) : '';
        */

        $this->vars['hasExtensionSettings'] = $this->vars['hasExtension'] ? $this->confirm('Does the extension have settings?', false) : false;

        $this->vars['extensionHooks'] = array();

        if ($this->vars['hasExtension']) {
            $this->askForHook($this->vars['extensionHooks']);
        }

        $this->vars['hasModuleMod'] = $this->vars['hasModule'] ? $this->confirm('Does the module need a mod file for template tags?') : false;

        $this->vars['hasModuleMcp'] = $this->vars['hasModule'] ? $this->confirm('Does the module need an mcp file for control panel views or actions?') : false;

        $this->vars['hasModuleCp'] = $this->vars['hasModule'] && $this->vars['hasModuleMcp'] ? $this->confirm('Does the module have a control panel backend?') : false;

        $this->vars['hasModuleTab'] = $this->vars['hasModule'] ? $this->confirm('Does the module have a publish tab?', false) : false;

        $this->vars['hasFieldtypeSettings'] = $this->vars['hasFieldtype'] ? $this->confirm('Does the fieldtype have settings?', false) : false;

        $this->vars['fieldtypeSupport'] = array();

        if ($this->vars['hasFieldtype']) {
            $fieldtypeSupport = $this->choice(
                'Which field types do you want to support? (default: ALL)',
                array(
                    'ALL',
                    'matrix',
                    'grid',
                    'low_variables',
                    'content_elements',
                    'matrix + grid',
                    'matrix + grid + low_variables',
                    'matrix + grid + low_variables + content_elements',
                    'matrix + grid + content_elements',
                    'grid + low_variables',
                    'grid + low_variables + content_elements',
                    'grid + content_elements',
                    'low_variables + content_elements',
                ),
                '0',
                null,
                true
            );

            $this->vars['fieldtypeSupport'] = array();

            foreach ($fieldtypeSupport as $type) {
                foreach (explode(' + ', $type) as $type) {
                    $this->vars['fieldtypeSupport'][] = $type;
                }
            }

            $this->vars['fieldtypeSupport'] = array_unique($this->vars['fieldtypeSupport']);
        }

        $this->vars['fieldtypeMatrixSupport'] = in_array('matrix', $this->vars['fieldtypeSupport']);
        $this->vars['fieldtypeGridSupport'] = in_array('grid', $this->vars['fieldtypeSupport']);
        $this->vars['fieldtypeLowVariablesSupport'] = in_array('low_variables', $this->vars['fieldtypeSupport']);
        $this->vars['fieldtypeContentElementsSupport'] = in_array('content_elements', $this->vars['fieldtypeSupport']);

        $this->vars['hasFieldtypeGlobalSettings'] = $this->vars['hasFieldtype'] ? $this->confirm('Does the fieldtype have global settings?', false) : false;

        $this->vars['hasFieldtypeTagPair'] = $this->vars['hasFieldtype'] ? $this->confirm('Does the fieldtype have a tag pair?', false) : false;

        $this->vars['hasLang'] = $this->vars['hasExtensionSettings'] || $this->vars['hasModule'];
        $this->vars['currentYear'] = date('Y');
        $this->vars['fileName'] = ucfirst($this->vars['addonSlug']);

        // transform these bools to strings
        $this->vars['hasExtensionSettings'] = $this->vars['hasExtensionSettings'] ? 'y' : 'n';
        $this->vars['hasModuleCp'] = $this->vars['hasModuleCp'] ? 'y' : 'n';
        $this->vars['hasModuleTab'] = $this->vars['hasModuleTab'] ? 'y' : 'n';
        $this->vars['hasFieldtypeTagPair'] = $this->vars['hasFieldtypeTagPair'] ? 'TRUE' : 'FALSE';

        $folder = $systemPath.$this->vars['addonSlug'];

        $this->mkdir($folder);

        if ($this->vars['hasTheme']) {
            $themeFolder = $themePath.$this->vars['addonSlug'];

            $this->mkdir($themeFolder);

            copy(__DIR__.'/../templates/addon/index.html', $themeFolder.'/index.html');
        }

        // Install module files
        if ($this->vars['hasModule']) {
            $this->template('upd.php.handlebars', $folder.'/upd.'.$this->vars['addonSlug'].'.php');

            if ($this->vars['hasModuleMod']) {
              $this->template('mod.php.handlebars', $folder.'/mod.'.$this->vars['addonSlug'].'.php');
            }
            if (! $this->vars['hasModuleMod'] || $this->vars['hasModuleMcp']) {
              $this->template('mcp.php.handlebars', $folder.'/mcp.'.$this->vars['addonSlug'].'.php');
            }
        }

        if ($this->vars['hasLang']) {
            $this->mkdir($folder.'/language');
            $this->mkdir($folder.'/language/english');

            $this->template('lang.php.handlebars', $folder.'/language/english/'.$this->vars['addonSlug'].'_lang.php');
        }

        if ($this->vars['hasExtension']) {
            $this->template('ext.php.handlebars', $folder.'/ext.'.$this->vars['addonSlug'].'.php');
        }

        if ($this->vars['hasPlugin']) {
            $this->template('pi.php.handlebars', $folder.'/pi.'.$this->vars['addonSlug'].'.php');
        }

        if ($this->vars['hasAccessory']) {
            $this->template('acc.php.handlebars', $folder.'/acc.'.$this->vars['addonSlug'].'.php');
        }

        if ($this->vars['hasFieldtype']) {
            $this->template('ft.php.handlebars', $folder.'/ft.'.$this->vars['addonSlug'].'.php');
        }

        $this->info($this->vars['addonName'].' created.');
    }
}
