<?php

namespace eecli\Command;

use Illuminate\Console\Command;

abstract class AbstractCommand extends Command
{
    /**
     * @var array
     */
    private $fieldNameIdMap = array(
        'channel' => array(
            'table' => 'channels',
            'idField' => 'channel_id',
            'keyField' => 'channel_name'
        ),
        'cat_group' => array(
            'table' => 'category_groups',
            'idField' => 'group_id',
            'keyField' => 'group_name'
        ),
        'category' => array(
            'table' => 'categories',
            'idField' => 'cat_id',
            'keyField' => 'cat_url_title'
        ),
        'field_group' => array(
            'table' => 'field_groups',
            'idField' => 'group_id',
            'keyField' => 'group_name'
        ),
        'field' => array(
            'table' => 'channel_fields',
            'idField' => 'field_id',
            'keyField' => 'field_name'
        ),
        'status_group' => array(
            'table' => 'status_groups',
            'idField' => 'group_id',
            'keyField' => 'group_name'
        ),
        'status' => array(
            'table' => 'statuses',
            'idField' => 'status_id',
            'keyField' => 'status'
        ),
        'member' => array(
            'table' => 'members',
            'idField' => 'member_id',
            'keyField' => 'username'
        ),
        'member_group' => array(
            'table' => 'member_groups',
            'idField' => 'group_id',
            'keyField' => 'group_title'
        ),
        'upload_dirs' => array(
            'table' => 'upload_prefs',
            'idField' => 'id',
            'keyField' => 'name'
        )
    );

    /**
     * @param $key
     * @param $value
     * @return array|mixed
     */
    public function transformKeyToId($key, $value)
    {
        if (true === is_array($value)) {
            $tmp = array();
            foreach ($value as $v) {
                $tmp[] = $this->transformKeyToId($key, $v);
            }
            $value = $tmp;
        } else {
            if (
                true === array_key_exists($key, $this->fieldNameIdMap)
                && false === empty($value)
                && false === is_numeric($value)
            ) {
                $query = ee()->db->select($this->fieldNameIdMap[$key]['idField'])
                    ->where($this->fieldNameIdMap[$key]['keyField'], $value)
                    ->limit(1)
                    ->get($this->fieldNameIdMap[$key]['table']);

                if ($query->num_rows() > 0) {
                    $value = $query->row($this->fieldNameIdMap[$key]['idField']);
                }

                $query->free_result();
            };
        }
        return $value;
    }
}
