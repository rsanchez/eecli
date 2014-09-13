<?php

namespace eecli\Command;

use Illuminate\Console\Command;

class ShowTemplatesCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'show:templates';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Show a list of templates.';

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        $query = ee()->db->from('templates')
            ->select('template_id')
            ->select("CONCAT(IF (is_site_default = 'y' AND template_name = 'index', '*', ''), group_name, '/', template_name) AS template", false)
            ->join('template_groups', 'template_groups.group_id = templates.group_id')
            ->order_by('group_order', 'asc')
            ->order_by('template_name', 'asc')
            ->get();

        $templates = $query->result_array();

        $query->free_result();

        $this->table(array('ID', 'Template'), $templates);
    }
}
