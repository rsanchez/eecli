<?php

global $vendor_path;

if (! isset($vendor_path)) {
    if (file_exists(__DIR__.'/../vendor/autoload.php')) {
        $vendor_path = __DIR__.'/../vendor/';
    } else {
        $vendor_path = __DIR__.'/../../../';
    }
}

require $vendor_path.'autoload.php';

// these must be initialized in the global context
$system_path = null;
$assign_to_config = array();

$app = new eecli\Application();

$app->setVendorPath($vendor_path);

$app->fire('bootstrap.before');

if ($app->canBeBootstrapped()) {
    require $vendor_path.'eecli/bootstrap/bootstrap-ee2.php';
}

$app->fire('bootstrap.after');

$app->addComposerCommands();
$app->addConditionalCommands();
$app->addThirdPartyCommands();
$app->addUserDefinedCommands();
$app->addGlobalCommands();

$app->run();
