<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class GithubAddonInstallerCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'install:addon';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Install an addon (requires Github Addon Installer module).';

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
        $manifestFile = PATH_THIRD.'github_addon_installer/config/manifest.js';

        if (! file_exists($manifestFile)) {
            $this->error('Could not find the Github Addon Installer manifest.');

            return;
        }

        $manifestContents = file_get_contents($manifestFile);

        if ($manifestContents === false) {
            $this->error('Could not load the Github Addon Installer manifest.');

            return;
        }

        $manifest = json_decode($manifestContents, true);

        if (! $manifest) {
            $this->error('Could not load the Github Addon Installer manifest.');

            return;
        }

        ksort($manifest);

        $addon = $this->argument('addon');
        $branch = $this->argument('branch') ?: 'master';

        $askForBranch = false;

        $validation = function ($addon) use ($manifest) {
            if (! isset($manifest[$addon])) {
                throw new \InvalidArgumentException(sprintf('Addon "%s" is invalid.', $addon));
            }

            return $addon;
        };

        if ($addon) {
            try {
                call_user_func($validation, $addon);
            } catch (\Exception $e) {

                $this->error($e->getMessage());

                return;
            }
        } else {
            $dialog = $this->getHelper('dialog');

            $addon = $dialog->askAndValidate($this->output, 'Which addon do you want to install? ', $validation, false, null, array_keys($manifest));

            $askForBranch = true;
        }

        $params = $manifest[$addon];

        $params['name'] = $addon;

        if ($askForBranch) {
            $branch = isset($params['branch']) ? $params['branch'] : 'master';

            $params['branch'] = $dialog->ask(
                $this->output,
                sprintf('Which branch would you like to install? (Defaults to %s) ', $branch),
                $branch
            );
        }

        ee()->load->add_package_path(PATH_THIRD.'github_addon_installer/');
        ee()->load->library('github_addon_installer');
        ee()->load->remove_package_path(PATH_THIRD.'github_addon_installer/');

        try {
            $repo = ee()->github_addon_installer->repo($params);

            try {
                $repo->install();
            } catch (\Exception $e) {
                $this->error($e->getMessage());

                return;
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());

            return;
        }

        $this->info($addon.' installed.');
    }
}
