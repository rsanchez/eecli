<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use eecli\GithubAddonInstaller\Application as InstallerApplication;
use eecli\GithubAddonInstaller\Api;
use eecli\GithubAddonInstaller\Repo;
use eecli\GithubAddonInstaller\Installer\Installer;

class GithubAddonInstallerCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'install:addon';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Install an addon.';

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
}
