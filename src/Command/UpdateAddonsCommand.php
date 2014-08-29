<?php

namespace eecli\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

class UpdateAddonsCommand extends Command
{
    protected function configure()
    {
        $this->setName('update:addons');
        $this->setDescription('Run addon updates.');

        $this->addArgument(
            'type',
            InputArgument::OPTIONAL,
            'Which addon type do you want to update? modules, extensions, fieldtypes, or accessories? (Leave blank to update all)'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $validTypes = array('modules', 'extensions', 'fieldtypes', 'accessories', 'all');

        $type = $input->getArgument('type') ?: 'all';

        if (! in_array($type, $validTypes)) {
            $output->writeln('<error>Invalid addon type.</error>');

            return;
        }

        if ($type === 'all' || $type === 'modules') {
            $this->updateModules($output);
        }

        if ($type === 'all' || $type === 'extensions') {
            $this->updateExtensions($output);
        }

        if ($type === 'all' || $type === 'fieldtypes') {
            $this->updateFieldtypes($output);
        }

        if ($type === 'all' || $type === 'accessories') {
            $this->updateAccessories($output);
        }
    }

    protected function updateModules(OutputInterface $output)
    {
        ee()->load->library('addons');

        $modules = ee()->addons->get_installed('modules');

        $modulesUpdated = 0;

        foreach ($modules as $name => $data) {
            $updaterPath = $data['path'].sprintf('upd.%s.php', $name);

            if (! file_exists($updaterPath)) {
                continue;
            }

            $moduleClass = ucfirst($name);

            $updaterClass = sprintf('%s_upd', $moduleClass);

            require_once $updaterPath;

            if (! method_exists($updaterClass, 'update') || ! is_callable(array($updaterClass, 'update'))) {
                continue;
            }

            ee()->load->add_package_path($data['path']);

            $updater = new $updaterClass;

            $updater->_ee_path = APPPATH;

            if (version_compare($data['module_version'], $updater->version, '<')) {
                $updated = $updater->update($data['module_version']);

                if ($updated) {
                    ee()->db->where('module_name', $moduleClass)
                        ->update('modules', array('module_version' => $updater->version));

                    $modulesUpdated++;

                    $output->writeln(sprintf('<comment>%s updated from %s to %s.</comment>', $name, $data['module_version'], $updater->version));
                }
            }

            ee()->load->remove_package_path($data['path']);
        }

        if ($modulesUpdated > 0) {
            $output->writeln('<info>Modules updated.</info>');
        } else {
            $output->writeln('<info>Modules already up-to-date.</info>');
        }
    }

    protected function updateExtensions(OutputInterface $output)
    {
        ee()->load->model('addons_model');

        if (ee()->config->item('allow_extensions') !== 'y') {
            $output->writeln('<comment>Extensions not enabled.</comment>');
            return;
        }

        $extensions = ee()->addons->get_installed('extensions');

        $extensionsUpdated = 0;

        foreach($extensions as $name => $data) {
            require_once $data['path'].$data['file'];

            if (! method_exists($data['class'], 'update_extension') || ! is_callable(array($data['class'], 'update_extension'))) {
                continue;
            }

            ee()->load->add_package_path($data['path']);

            $extension = new $data['class'];

            if (version_compare($data['version'], $extension->version, '<')) {
                $extension->update_extension($data['version']);

                ee()->addons_model->update_extension($data['class'], array('version' => $extension->version));

                $extensionsUpdated++;

                $output->writeln(sprintf('<comment>%s updated from %s to %s.</comment>', $data['name'], $data['version'], $extension->version));
            }

            ee()->load->remove_package_path($data['path']);
        }

        if ($extensionsUpdated > 0) {
            $output->writeln('<info>Extensions updated.</info>');
        } else {
            $output->writeln('<info>Extensions already up-to-date.</info>');
        }
    }

    protected function updateFieldtypes(OutputInterface $output)
    {
        ee()->load->library('api');

        ee()->api->instantiate('channel_fields');

        $fieldtypes = ee()->addons->get_installed('fieldtypes');

        $fieldtypesUpdated = 0;

        foreach ($fieldtypes as $name => $data) {
            if (! ee()->api_channel_fields->include_handler($name)) {
                continue;
            }

            $fieldtype = ee()->api_channel_fields->setup_handler($name, true);

            if (! method_exists($fieldtype, 'update') || ! is_callable(array($fieldtype, 'update'))) {
                continue;
            }

            if (version_compare($data['version'], $fieldtype->info['version'], '>=')) {
                continue;
            }

            $updated = ee()->api_channel_fields->apply('update', array($data['version']));

            if ($updated) {
                ee()->db->where('name', $name)
                    ->update('fieldtypes', array('version' => $fieldtype->info['version']));

                $fieldtypesUpdated++;

                $output->writeln(sprintf('<comment>%s updated from %s to %s.</comment>', $name, $data['version'], $fieldtype->info['version']));
           }
        }

        if ($fieldtypesUpdated > 0) {
            $output->writeln('<info>Fieldtypes updated.</info>');
        } else {
            $output->writeln('<info>Fieldtypes already up-to-date.</info>');
        }
    }

    protected function updateAccessories(OutputInterface $output)
    {
        ee()->load->model('addons_model');

        $accessories = ee()->addons->get_installed('accessproes');

        $accessoriesUpdated = 0;

        foreach ($accessories as $name => $data) {
            require_once $data['path'].$data['file'];

            if (! method_exists($data['class'], 'update') || ! is_callable(array($data['class'], 'update'))) {
                continue;
            }

            if (! empty($data['package'])) {
                ee()->load->add_package_path($data['path']);
            }

            $accessory = new $data['class'];

            if (version_compare($data['accessory_version'], $accessory->version, '<')) {
                $updated = $accessory->update();

                if ($updated) {
                    ee()->addons_model->update_accessory($data['class'], array('accessory_version' => $accessory->version));

                    $accessoriesUpdated++;

                    $output->writeln(sprintf('<comment>%s updated from %s to %s.</comment>', $name, $data['accessory_version'], $accessory->version));
                }
            }

            if (! empty($data['package'])) {
                ee()->load->remove_package_path($data['path']);
            }
        }

        if ($accessoriesUpdated > 0) {
            $output->writeln('<info>Accessories updated.</info>');
        } else {
            $output->writeln('<info>Accessories already up-to-date.</info>');
        }
    }
}