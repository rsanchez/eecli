<?php

namespace eecli\Command;

use eecli\Application;
use eecli\Command\Contracts\HasRuntimeOptions;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;

class CreateCategoryCommand extends Command implements HasRuntimeOptions
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'create:category';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a category.';

    /**
     * {@inheritdoc}
     */
    protected function getArguments()
    {
        return array(
            array(
                'name', // name
                InputArgument::REQUIRED, // mode
                'The category name.', // description
            ),
            array(
                'category_group', // name
                InputArgument::REQUIRED, // mode
                'The category group ID or name.', // description
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
                'url_title', // name
                'u', // shortcut
                InputOption::VALUE_REQUIRED, // mode
                'The url title of the category.', // description
                '', // default value
            ),
            array(
                'description', // name
                'd', // shortcut
                InputOption::VALUE_REQUIRED, // mode
                'The description of the category.', // description
                '', // default value
            ),
            array(
                'parent_id', // name
                'p', // shortcut
                InputOption::VALUE_REQUIRED, // mode
                'The ID of the parent category.', // description
                '', // default value
            ),
        );
    }

    /**
     * Get the category group ID from a name or number
     * @param  string $group the name of a group or the ID of the group
     * @return string the group ID
     * @throws \RuntimeException if the category group is not found
     */
    protected function getCategoryGroupId($group)
    {
        if (is_numeric($group)) {
            ee()->db->where('group_id', $group);
        } else {
            ee()->db->where('group_name', $group);
        }

        $query = ee()->db->select('group_id')
            ->get('category_groups');

        if ($query->num_rows() === 0) {
            throw new \RuntimeException('Invalid group.');
        }

        $groupId = $query->row('group_id');

        $query->free_result();

        return $groupId;
    }

    /**
     * Get category fields by group ID
     * @param  string $groupId
     * @return array  of \stdClass
     */
    protected function getCategoryGroupFields($groupId)
    {
        $query = ee()->db->where('group_id', $groupId)
            ->order_by('field_order', 'asc')
            ->get('category_fields');

        $fields = $query->result();

        $query->free_result();

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function getRuntimeOptions(Application $app, InputInterface $input)
    {
        $group = $input->getArgument('category_group');

        $groupId = $this->getCategoryGroupId($group);

        $fields = $this->getCategoryGroupFields($groupId);

        foreach ($fields as $field) {
            $options[] = new InputOption(
                $field->field_name,
                null,
                InputOption::VALUE_REQUIRED,
                $field->field_label
            );
        }

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        $this->getApplication()->newInstance('\\eecli\\CodeIgniter\\Controller\\AdminContentController');

        $name = $this->argument('name');

        $group = $this->argument('category_group');

        $groupId = $this->getCategoryGroupId($group);

        $fields = $this->getCategoryGroupFields($groupId);

        $_SERVER['CONTENT_LENGTH'] = 0;

        $_POST = array(
            'group_id' => $groupId,
            'cat_name' => $name,
            'cat_url_title' => $this->option('url_title'),
            'cat_description' => $this->option('description'),
            'cat_image_hidden_file' => '',
            'cat_image_hidden_dir' => '',
            'cat_image_directory' => '',
            'parent_id' => $this->option('parent_id'),
        );

        foreach ($fields as $field) {
            $_POST['field_ft_'.$field->field_id] = $field->field_default_fmt;

            $value = $this->option($field->field_name);

            if ($field->field_required === 'y' && empty($value)) {
                $this->error(sprintf('The --%s option is required for this category group.', $field->field_name));

                return;
            }

            $_POST['field_id_'.$field->field_id] = $value;
        }

        ee()->category_update();

        if (ee()->output->getErrorMessage()) {
            $this->error(ee()->output->getErrorMessage());

            return;
        }

        if (ee()->form_validation->_error_messages) {
            foreach (ee()->form_validation->_error_messages as $error) {
                $this->error($error);
            }

            return;
        }

        $query = ee()->db->select('cat_id')
            ->where('cat_name', $name)
            ->where('group_id', $groupId)
            ->order_by('cat_id', 'desc')
            ->get('categories');

        $this->info(sprintf('Category %s (%s) created.', $name, $query->row('cat_id')));

        $query->free_result();
    }
}
