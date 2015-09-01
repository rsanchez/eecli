<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;
use eecli\Command\Contracts\HasLongDescription;
use Symfony\Component\Console\Input\InputArgument;

class UpdateAddonsCommand extends Command implements HasExamples, HasLongDescription
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'update:addons';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Run addon updates.';

    /**
     * {@inheritdoc}
     */
    protected function getArguments()
    {
        return array(
            array(
                'type',
                InputArgument::OPTIONAL,
                'Which addon type do you want to update? modules, extensions, fieldtypes, or accessories? (Leave blank to update all)',
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        $validTypes = array('modules', 'extensions', 'fieldtypes', 'accessories', 'all');

        $type = $this->argument('type') ?: 'all';

        if (! in_array($type, $validTypes)) {
            $this->error('Invalid addon type.');

            return;
        }

        if ($type === 'all' || $type === 'modules') {
            $this->updateModules();
        }

        if ($type === 'all' || $type === 'extensions') {
            $this->updateExtensions();
        }

        if ($type === 'all' || $type === 'fieldtypes') {
            $this->updateFieldtypes();
        }

        if ($type === 'all' || $type === 'accessories') {
            $this->updateAccessories();
        }
    }

    /**
     * Run module updates
     * @return void
     */
    protected function updateModules()
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

            $updater = new $updaterClass();

            $updater->_ee_path = APPPATH;

            if (version_compare($data['module_version'], $updater->version, '<')) {
                $updated = $updater->update($data['module_version']);

                if ($updated) {
                    ee()->db->where('module_name', $moduleClass)
                        ->update('modules', array('module_version' => $updater->version));

                    $modulesUpdated++;

                    $this->comment(sprintf('%s updated from %s to %s.', $name, $data['module_version'], $updater->version));
                }
            }

            ee()->load->remove_package_path($data['path']);
        }

        if ($modulesUpdated > 0) {
            $this->info('Modules updated.');
        } else {
            $this->info('Modules already up-to-date.');
        }
    }

    /**
     * Run extension updates
     * @return void
     */
    protected function updateExtensions()
    {
        ee()->load->model('addons_model');

        ee()->load->library('addons');

        if (ee()->config->item('allow_extensions') !== 'y') {
            $this->comment('Extensions not enabled.');

            return;
        }

        $extensions = ee()->addons->get_installed('extensions');

        $extensionsUpdated = 0;

        foreach ($extensions as $name => $data) {
            require_once $data['path'].$data['file'];

            if (! method_exists($data['class'], 'update_extension') || ! is_callable(array($data['class'], 'update_extension'))) {
                continue;
            }

            ee()->load->add_package_path($data['path']);

            $class = $data['class'];

            $extension = new $class();

            if (version_compare($data['version'], $extension->version, '<')) {
                $extension->update_extension($data['version']);

                ee()->addons_model->update_extension($data['class'], array('version' => $extension->version));

                $extensionsUpdated++;

                $this->comment(sprintf('%s updated from %s to %s.', $data['name'], $data['version'], $extension->version));
            }

            ee()->load->remove_package_path($data['path']);
        }

        if ($extensionsUpdated > 0) {
            $this->info('Extensions updated.');
        } else {
            $this->info('Extensions already up-to-date.');
        }
    }

    /**
     * Run fieldtype updates
     * @return void
     */
    protected function updateFieldtypes()
    {
        ee()->load->library('api');

        ee()->api->instantiate('channel_fields');

        ee()->load->library('addons');

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

                $this->comment(sprintf('%s updated from %s to %s.', $name, $data['version'], $fieldtype->info['version']));
            }
        }

        if ($fieldtypesUpdated > 0) {
            $this->info('Fieldtypes updated.');
        } else {
            $this->info('Fieldtypes already up-to-date.');
        }
    }

    /**
     * Run accessory updates
     * @return void
     */
    protected function updateAccessories()
    {
        ee()->load->model('addons_model');

        ee()->load->library('addons');

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

            $class = $data['class'];

            $accessory = new $class();

            if (version_compare($data['accessory_version'], $accessory->version, '<')) {
                $updated = $accessory->update();

                if ($updated) {
                    ee()->addons_model->update_accessory($data['class'], array('accessory_version' => $accessory->version));

                    $accessoriesUpdated++;

                    $this->comment(sprintf('%s updated from %s to %s.', $name, $data['accessory_version'], $accessory->version));
                }
            }

            if (! empty($data['package'])) {
                ee()->load->remove_package_path($data['path']);
            }
        }

        if ($accessoriesUpdated > 0) {
            $this->info('Accessories updated.');
        } else {
            $this->info('Accessories already up-to-date.');
        }
    }

    public function getLongDescription()
    {
        return 'This checks if any of your addons (modules, extensions, and fieldtypes) are out of date by comparing version numbers in your database with version numbers in your addon files. If so, it will run the addon\'s update method. This is exactly how addon updates work inside the control panel.';
    }

    public function getExamples()
    {
        return array(
            'run all addon updates' => '',
            'run module updates' => 'modules',
            'run extension updates' => 'extensions',
            'run fieldtype updates' => 'fieldtypes',
            'run accessory updates' => 'accessories',
        );
    }
}
