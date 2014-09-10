<?php

namespace eecli\CodeIgniter;

require_once APPPATH.'libraries/Cp.php';

class Cp extends \Cp
{
    /**
     * @var array
     */
    protected $variables = array();

    public function __construct($theme = '', $themeUrl = '')
    {
        $this->cp_theme = $theme;
        $this->cp_theme_url = $themeUrl;
    }

    /**
     * Get the stored view variables
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * Override the parent class' render method
     *
     * Don't render anything, simply save the view
     * variables for later usage. See getViewData().
     * @param  string  $view
     * @param  array   $data
     * @param  boolean $return
     * @return void
     */
    public function render($view, $data = array(), $return = false)
    {
        $this->variables = array_merge($this->variables, $data);
    }
}
