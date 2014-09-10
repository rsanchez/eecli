<?php

namespace eecli\CodeIgniter;

abstract class Loader extends \EE_Loader
{
    /**
     * Override a base class in the CI loader
     * @param string $which  short name of lib
     * @param object $object instance of lib
     */
    public static function addBaseClass($which, $object)
    {
        ee()->$which = ee()->load->_base_classes[$which] = $object;
    }
}
