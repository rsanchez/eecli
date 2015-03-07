<?php

namespace eecli\Command;

use eecli\Command\Contracts\Conditional;
use eecli\Command\Contracts\HasOptionExamples;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

abstract class AbstractCreateFieldCommand extends Command implements Conditional, HasOptionExamples
{
    /**
     * List of fieldtypes installed here
     * @var array
     */
    protected static $fieldtypesInstalled;

    /**
     * The name of the fieldtype, e.g. 'text'
     * @return string
     */
    abstract protected function getFieldtype();

    /**
     * Load up all fieldtypes so we can determine which
     * are applicable
     * @return void
     */
    protected static function loadInstalledFieldtypes()
    {
        if (is_null(static::$fieldtypesInstalled)) {
            static::$fieldtypesInstalled = [];

            $query = ee()->db->select('name')
                ->get('fieldtypes');

            foreach ($query->result() as $row) {
                static::$fieldtypesInstalled[$row->name] = true;
            }

            $query->free_result();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable()
    {
        static::loadInstalledFieldtypes();

        return isset(static::$fieldtypesInstalled[$this->getFieldtype()]);
    }

    /**
     * Array of InputOption objects for this fieldtype
     * @return array of \Symfony\Component\Console\Input\InputOption
     */
    protected function getFieldtypeOptions()
    {
        return array();
    }

    /**
     * Array of fieldtype settings, to be merge into $_POST
     * @return array
     */
    protected function getFieldtypeSettings()
    {
        return array();
    }

    protected function getFieldtypeOptionExamples()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->name = 'create:field:'.$this->getFieldtype();

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function getArguments()
    {
        return array(
            array(
                'label', // name
                InputArgument::REQUIRED, // mode
                'The label of the field.', // description
            ),
            array(
                'short_name', // name
                InputArgument::REQUIRED, // mode
                'The short name of the field.', // description
            ),
            array(
                'field_group', // name
                InputArgument::REQUIRED, // mode
                'The ID or name of the field group.', // description
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getOptions()
    {
        return array_merge(array(
            array(
                'instructions',
                null,
                InputOption::VALUE_REQUIRED,
                'Instructions for authors on how or what to enter into this field when submitting an entry.',
                '',
            ),
            array(
                'required',
                null,
                InputOption::VALUE_NONE,
                'Make this field required',
            ),
            array(
                'searchable',
                null,
                InputOption::VALUE_NONE,
                'Make this field searchable',
            ),
            array(
                'hidden',
                null,
                InputOption::VALUE_NONE,
                'Make this field hidden',
            ),
            array(
                'order',
                null,
                InputOption::VALUE_REQUIRED,
                'Set this field\'s order',
            ),
        ), $this->getFieldtypeOptions());
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        $instance = $this->getApplication()->newControllerInstance('\\eecli\\CodeIgniter\\Controller\\AdminContentController');

        $groupId = $this->argument('field_group');

        if (! is_numeric($groupId)) {
            $query = ee()->db->select('group_id')
                ->where('group_name', $groupId)
                ->limit(1)
                ->get('field_groups');

            if ($query->num_rows() > 0) {
                $groupId = $query->row('group_id');
            }

            $query->free_result();
        }

        $name = $this->argument('short_name');

        // get field order
        $order = $this->option('order');

        if (! $order && $order !== '0') {
            $query = $instance->db->select('field_order')
                ->where('group_id', $groupId)
                ->order_by('field_order', 'desc')
                ->limit(1)
                ->get('channel_fields');

            if ($query->num_rows() > 0) {
                $order = $query->row('field_order') + 1;
            }

            $query->free_result();
        }

        $_POST = array(
            'site_id' => $instance->config->item('site_id'),
            'group_id' => $groupId,
            'field_label' => $this->argument('label'),
            'field_name' => $name,
            'field_type' => $this->getFieldtype(),
            'field_instructions' => $this->option('instructions'),
            'field_required' => $this->option('required') ? 'y' : 'n',
            'field_search' => $this->option('searchable') ? 'y' : 'n',
            'field_is_hidden' => $this->option('hidden') ? 'y' : 'n',
            'field_order' => $order,
            'field_maxl' => '128',
            'field_ta_rows' => '6',
        );

        $_POST = array_merge($_POST, $this->getFieldtypeSettings());

        $method = version_compare(APP_VER, '2.7', '<') ? 'field_update' : 'field_edit';

        $instance->$method();

        $this->getApplication()->checkForErrors(true);

        $query = ee()->db->select('field_id')
            ->where('field_name', $name)
            ->where('group_id', $groupId)
            ->get('channel_fields');

        $this->info(sprintf('Field %s (%s) created.', $name, $query->row('field_id')));

        $query->free_result();
    }

    public function getOptionExamples()
    {
        $optionExamples = array(
            'instructions' => 'Your instructions here.',
            'order' => '1',
            'content_type' => 'all',
        );

        return array_merge($optionExamples, $this->getFieldtypeOptionExamples());
    }
}
