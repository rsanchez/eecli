<?php

namespace eecli\Command;

use eecli\Command\Contracts\ExemptFromBootstrap;
use eecli\Command\Contracts\HasLongDescription;
use Illuminate\Console\Command;
use Symfony\Component\Console\Question\Question;
use Handlebars\Handlebars;
use Handlebars\Loader\FilesystemLoader;

class GenerateAddonCommand extends Command implements ExemptFromBootstrap, HasLongDescription
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'generate:addon';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Generate an addon using a wizard interface.';

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

    /**
     * Path to handlebars templates
     * @var string
     */
    protected $templatePath;

    public function __construct()
    {
        parent::__construct();

        $this->templatePath = __DIR__.'/../../../addon-templates/app/templates';
    }

    /**
     * Add a validator that makes a question required
     * @param Question $question
     * @param string   $errorMessage
     */
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
     * Validate the answer as required.
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

    /**
     * Keep ask for a selection from the specified choices
     * until the user enters DONE.
     *
     * @param  string  $question
     * @param  array   $defaultChoices
     * @param  boolean $defaultToAll
     * @param  string  $secondaryQuestion
     * @return array
     */
    protected function askUntilDone($question, array $defaultChoices, $defaultToAll = false, $secondaryQuestion = '')
    {
        $userChoices = array();

        $remainingChoices = $defaultChoices;

        $countDefaultChoices = count($defaultChoices);

        do {
            $countRemainingChoices = count($remainingChoices);

            $questionChoices = $remainingChoices;

            if ($countRemainingChoices < $countDefaultChoices) {
                array_unshift($questionChoices, 'DONE');
            }

            if ($countRemainingChoices > 1) {
                $questionChoices[] = 'All of the above';
            }

            $default = $defaultToAll ? count($questionChoices) - 1 : 0;

            $choice = $this->choice(
                sprintf('%s (default: %s)', $question, $questionChoices[$default]),
                $questionChoices,
                $default,
                null
            );

            if ($choice) {
                if ($choice === 'All of the above') {
                    return $defaultChoices;
                }

                if ($secondaryQuestion) {
                    $question = $secondaryQuestion;
                }

                $userChoices[] = $choice;

                unset($remainingChoices[array_search($choice, $remainingChoices)]);

                $countRemainingChoices = count($remainingChoices);
            }

        } while ($countRemainingChoices > 0 && $choice !== 'DONE');

        return $userChoices;
    }

    /**
     * Ask for multiple extension hooks
     * @param  array $extensionHooks
     * @return void
     */
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

    /**
     * Create a directory if it doesn't already exist
     * @param  string $pathname
     * @return void
     */
    protected function mkdir($pathname)
    {
        if (! is_dir($pathname)) {
            if (! mkdir($pathname)) {
                throw new \RuntimeException('Could not create directory '.$pathname);
            }
        }
    }

    /**
     * Create nested directories, if they don't already exist
     * @param  string $pathname
     * @return void
     */
    protected function mkdirRecursive($pathname)
    {
        $folders = explode('/', $pathname);

        $pathname = '';

        foreach ($folders as $folder) {
            $pathname .= $folder.'/';

            $this->mkdir($pathname);
        }
    }

    /**
     * Render a file from a Handlebars template
     * @param  string $template    name of template
     * @param  string $destination where to save the file
     * @return void
     */
    protected function template($template, $destination)
    {
        $output = $this->handlebars->render($template, $this->vars);

        if (file_exists($destination) && ! $this->confirm($destination.' already exists. Do you want to overwrite? ', false)) {
            $this->error(basename($destination).' not created.');

            return;
        }

        $handle = fopen($destination, 'w');

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
            'loader' => new FilesystemLoader($this->templatePath),
        ));

        $systemPath = defined('PATH_THIRD') ? PATH_THIRD : null;
        $themePath = defined('PATH_THIRD_THEMES') ? PATH_THIRD_THEMES : null;

        $gitUser = trim(shell_exec('git config --get github.user'));
        $defaultAuthorName = $this->getApplication()->getAddonAuthorName() ?: $gitUser;
        $defaultAuthorUrl = $this->getApplication()->getAddonAuthorUrl();

        if (! $defaultAuthorUrl && $gitUser) {
            $defaultAuthorUrl = 'https://github.com/'.$gitUser;
        }

        $this->vars['addonTypes'] = $this->askUntilDone(
            'Which add-on type do you wish to create?',
            array(
                'Plugin',
                'Extension',
                'Module',
                'Fieldtype',
                'Accessory',
            ),
            false,
            'Do you want to create an additional add-on type?'
        );

        $this->vars['hasPlugin'] = in_array('Plugin', $this->vars['addonTypes']);
        $this->vars['hasExtension'] = in_array('Extension', $this->vars['addonTypes']);
        $this->vars['hasModule'] = in_array('Module', $this->vars['addonTypes']);
        $this->vars['hasFieldtype'] = in_array('Fieldtype', $this->vars['addonTypes']);
        $this->vars['hasAccessory'] = in_array('Accessory', $this->vars['addonTypes']);

        $this->vars['addonName'] = $this->askRequired('What do you want to name your add-on? ex. Google Maps', null, 'You must choose an add-on name.');

        $defaultAddonSlug = preg_replace('/[^a-z0-9_]/', '', preg_replace('/\s+/', '_', strtolower($this->vars['addonName'])));

        $this->vars['addonSlug'] = $this->askRequired("What slug name do you want to give your add-on?", $defaultAddonSlug, 'You must choose an add-on slug.');

        $this->vars['addonDescription'] = $this->vars['hasPlugin'] || $this->vars['hasExtension'] || $this->vars['hasModule'] || $this->vars['hasAccessory'] ? $this->ask('What is the description for this add-on?') : '';

        $this->vars['authorName'] = $this->ask('What is your name?', $defaultAuthorName);

        $this->vars['authorUrl'] = $this->ask('What is your URL?', $defaultAuthorUrl);

        $this->vars['hasTheme'] = $this->confirm('Does this add-on need theme files?', false);

        if (is_null($systemPath)) {
            $systemPath = $this->ask('What is the system path?', 'system/expressionengine/third_party/');
        }

        $this->mkdirRecursive($systemPath);

        if ($this->vars['hasTheme']) {
            if (is_null($themePath)) {
                $themePath = $this->ask('What is the theme path?', 'themes/third_party/');
            }

            $this->mkdirRecursive($themePath);
        }

        $this->vars['hasExtensionSettings'] = $this->vars['hasExtension'] ? $this->confirm('Does the extension have settings?', false) : false;

        $this->vars['extensionHooks'] = array();

        if ($this->vars['hasExtension']) {
            $this->askForHook($this->vars['extensionHooks']);
        }

        $this->vars['hasModuleMod'] = $this->vars['hasModule'] ? $this->confirm('Does the module need a mod file for template tags?') : false;

        $this->vars['hasModuleCp'] = $this->vars['hasModule'] ? $this->confirm('Does the module have a control panel backend?') : false;

        $this->vars['hasModuleTab'] = $this->vars['hasModule'] ? $this->confirm('Does the module have a publish tab?', false) : false;

        $this->vars['hasFieldtypeSettings'] = $this->vars['hasFieldtype'] ? $this->confirm('Does the fieldtype have settings?', false) : false;

        $this->vars['fieldtypeSupport'] = array();

        if ($this->vars['hasFieldtype']) {
            $this->vars['fieldtypeSupport'] = $this->askUntilDone(
                'Which extra field type do you want to support?',
                array(
                    'Matrix',
                    'Grid',
                    'Low Variables',
                    'Content Elements',
                ),
                true,
                'Do you want to support an additional extra field type?'
            );
        }

        $this->vars['fieldtypeMatrixSupport'] = in_array('Matrix', $this->vars['fieldtypeSupport']);
        $this->vars['fieldtypeGridSupport'] = in_array('Grid', $this->vars['fieldtypeSupport']);
        $this->vars['fieldtypeLowVariablesSupport'] = in_array('Low Variables', $this->vars['fieldtypeSupport']);
        $this->vars['fieldtypeContentElementsSupport'] = in_array('Content Elements', $this->vars['fieldtypeSupport']);

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

            copy($this->templatePath.'/index.html', $themeFolder.'/index.html');
        }

        // Install module files
        if ($this->vars['hasModule']) {
            $this->template('upd.php.handlebars', $folder.'/upd.'.$this->vars['addonSlug'].'.php');

            if ($this->vars['hasModuleMod']) {
              $this->template('mod.php.handlebars', $folder.'/mod.'.$this->vars['addonSlug'].'.php');
            }

            $this->template('mcp.php.handlebars', $folder.'/mcp.'.$this->vars['addonSlug'].'.php');
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

    public function getLongDescription()
    {
        return "Generate an addon using a wizard interface.\n\n![Screencast of addon generation](https://rsanchez.github.io/eecli/images/eecli-generate-addon.gif)";
    }
}
