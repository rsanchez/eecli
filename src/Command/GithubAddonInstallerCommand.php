<?php

namespace eecli\eecli\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GithubAddonInstallerCommand extends Command
{
    protected function configure()
    {
        $this->setName('install');
        $this->setDescription('Install addons (requires Github Addon Installer module)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manifestFile = PATH_THIRD.'github_addon_installer/config/manifest.js';

        if (! file_exists($manifestFile)) {
            $output->writeln('<error>Could not find the Github Addon Installer manifest.</error>');

            return;
        }

        $manifestContents = file_get_contents($manifestFile);

        if ($manifestContents === false) {
            $output->writeln('<error>Could not load the Github Addon Installer manifest.</error>');

            return;
        }

        $manifest = json_decode($manifestContents, true);

        if (! $manifest) {
            $output->writeln('<error>Could not load the Github Addon Installer manifest.</error>');

            return;
        }

        ksort($manifest);

        $validation = function ($addon) use ($manifest) {
            if (! isset($manifest[$addon])) {
                throw new \InvalidArgumentException(sprintf('Addon "%s" is invalid.', $addon));
            }

            return $addon;
        };

        $dialog = $this->getHelper('dialog');

        $addon = $dialog->askAndValidate($output, 'Which addon do you want to install? ', $validation, false, null, array_keys($manifest));

        $params = $manifest[$addon];

        $params['name'] = $addon;

        $branch = isset($params['branch']) ? $params['branch'] : 'master';

        $params['branch'] = $dialog->ask(
            $output,
            sprintf('Which branch would you like to install? (Defaults to %s) ', $branch),
            $branch
        );

        ee()->load->add_package_path(PATH_THIRD.'github_addon_installer/');
        ee()->load->library('github_addon_installer');
        ee()->load->remove_package_path(PATH_THIRD.'github_addon_installer/');

        try
        {
            $repo = ee()->github_addon_installer->repo($params);

            try
            {
                $repo->install();
            }
            catch(Exception $e)
            {
                $output->writeln('<error>'.$e->getMessage().'</error>');

                return;
            }
        }
        catch(Exception $e)
        {
            $output->writeln('<error>'.$e->getMessage().'</error>');

            return;
        }

        $output->writeln('<info>'.$addon.' installed.</info>');
    }
}