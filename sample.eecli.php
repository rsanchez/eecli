<?php

/**
 * Sample eecli Configuration file
 *
 * Rename this file to .eecli.php in your site root.
 */

// Quit if this is not being requested via the CLI
if (php_sapi_name() !== 'cli') {
    exit;
}

return array(
    /**
     * System path
     *
     * Specify the path to your EE system folder
     * If you leave this blank, it will assume your
     * folder is <current directory>/system
     */
    'system_path' => __DIR__.'/system',

    /**
     * Spoof $_SERVER variables
     *
     * This array will be merged with $_SERVER.
     *
     * When using php from the command line,
     * things like HTTP_HOST and DOCUMENT_ROOT
     * do not get set.
     *
     * Useful if you check for $_SERVER items
     * at runtime, like changing DB
     * credentials based on HTTP_HOST
     * in your config.php.
     */
    'server' => array(
        'HTTP_HOST' => 'localhost',
        'DOCUMENT_ROOT' => __DIR__,
        'REQUEST_URI' => '/',
        'REMOTE_ADDR' => '127.0.0.1',
        'HTTP_USER_AGENT' => 'eecli',
    ),

    /**
     * Assign variables to config
     */
    'assign_to_config' => array(
        #'foo' => 'bar',
    ),

    /**
     * Custom commands
     *
     * An array of Command class names of
     * custom commands.
     */
    'commands' => array(
        #'\\Your\\Custom\\Command',
    ),
);
