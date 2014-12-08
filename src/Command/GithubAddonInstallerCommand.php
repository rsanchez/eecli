<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasLongDescription;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use eecli\GithubAddonInstaller\Application as InstallerApplication;

class GithubAddonInstallerCommand extends Command implements HasLongDescription
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'install:addon';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Install a Github-hosted addon.';

    /**
     * {@inheritdoc}
     */
    protected function getArguments()
    {
        return array(
            array(
                'addon',
                InputArgument::OPTIONAL,
                'Which addon do you want to install?',
            ),
            array(
                'branch',
                InputArgument::OPTIONAL,
                'Which branch do you want to install? (Leave blank to install the master branch)',
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        $tempPath = APPPATH.'cache/github_addon_installer/';

        if (! is_dir($tempPath)) {
            mkdir($tempPath);
        }

        $installerApp = new InstallerApplication(PATH_THIRD, PATH_THIRD_THEMES, $tempPath);

        $installerApp->getApi()->setOutput($this->output);
        $installerApp->getApi()->setProgressHelper($this->getHelperSet()->get('progress'));

        $manifest = $installerApp->getManifest();

        $addon = $this->argument('addon');

        if (! $addon) {
            $dialog = $this->getHelper('dialog');

            $validation = function ($addon) use ($manifest) {
                if (! isset($manifest[$addon])) {
                    throw new \InvalidArgumentException(sprintf('Addon "%s" is invalid.', $addon));
                }

                return $addon;
            };

            $addon = $dialog->askAndValidate($this->output, 'Which addon do you want to install? ', $validation, false, null, array_keys($manifest));
        }

        $branch = $this->argument('branch') ?: isset($manifest[$addon]['branch']) ? $manifest[$addon]['branch'] : 'master';

        $installerApp->installAddon($addon, $branch);

        $this->info($addon.' installed.');
    }

    public function getLongDescription()
    {
        return <<<EOT
Install Github-hosted addons using the `install:addon` wizard.

![Screencast of addon installation](https://github.com/rsanchez/eecli/wiki/images/install:addon.gif)

```
eecli install:addon
```

This will prompt you to enter an addon name. Start typing to trigger autocomplete.

If you already know a particular addon exists in the [Github Addon Installer repository](https://github.com/rsanchez/github_addon_installer/blob/master/system/expressionengine/third_party/github_addon_installer/config/manifest.js), you may simply specify the addon name as the first argument in the command. You can specify a branch as the second argument.

```
eecli install low_replace
eecli install stash dev
```
EOT;
    }
}
