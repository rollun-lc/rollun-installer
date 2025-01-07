<?php

use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\ServiceManager;

// Load configuration
$config = require __DIR__ . '/config.php';

// Build container
$container = new ServiceManager();
if(isset($config['dependencies'])) {
    (new Config($config['dependencies']))->configureServiceManager($container);
}

// Inject config
$container->setService('config', $config);

return $container;
