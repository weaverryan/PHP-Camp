<?php
use Pimple\Container;
use Aura\Router\RouterFactory;
use Zend\Log\Writer\Stream;
use Zend\Log\Logger;


$c = new Container();

// configuration
$c['connection_string'] = 'sqlite:' . __DIR__ . '/data/database.sqlite';
$c['log_path'] = __DIR__ . '/data/web.log';

// Service setup
$c['connection'] = function (Container $c) {
    return new PDO($c['connection_string']);
};

$c['router'] = function () {
    $routerFactory = new RouterFactory();
    $router = $routerFactory->newInstance();

    return $router;
};
$c['logger_writer'] = function (Container $c) {
    return new Stream($c['log_path']);
};
$c['logger'] = function (Container $c) {
    $logger = new Logger();
    $logger->addWriter($c['logger_writer']);

    return $logger;
};

return $c;
