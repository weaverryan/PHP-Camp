<?php

require __DIR__.'/vendor/autoload.php';

use Pimple\Container;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Aura\Dispatcher\Dispatcher;
use Aura\Router\RouterFactory;
use Zend\Log\Writer\Stream;
use Zend\Log\Logger;

/*
 * *************** Our one-method framework
 */

function _run_app(Container $c) {
    // run the framework!
    $route = $c['router']->match(
        $c['request']->getPathInfo(),
        $c['request']->server->all()
    );
    $params = array('controller' => 'error404_controller');
    // merge the matched attributes back into Symfony's request
    if ($route) {
        $params = $route->params;
        $c['request']->attributes->add($params);
        $c['logger']->info(sprintf('Found controller "%s"', $controller));
    } else {
        $msg = sprintf('Controller not found for "%s"', $c['request']->getPathInfo());
        $c['logger']->err($msg);
    }

    $params['request'] = $c['request'];
    $params['c'] = $c;
    $dispatcher = $c['dispatcher'];
    // execute the controller and get the response
    $response = $dispatcher->__invoke($params);
    if (!$response instanceof Response) {
        throw new Exception(sprintf('Your controller "%s" did not return a response!!', $controller));
    }

    return $response;
}

/*
 * *************** Container Setup
 */

$c = new Container();

// configuration
$c['connection_string'] = 'sqlite:'.__DIR__.'/data/database.sqlite';
$c['log_path'] = __DIR__.'/data/web.log';

// Service setup
$c['connection'] = function(Container $c) {
    return new PDO($c['connection_string']);
};

$c['dispatcher'] = function() {
    $dispatcher = new Dispatcher;
    $dispatcher->setObjectParam('controller');
    return $dispatcher;
};

$c['request'] = function() {
    return Request::createFromGlobals();
};

$c['router'] = function() {
    $routerFactory = new RouterFactory();

    $router = $routerFactory->newInstance();

    // create a router, build the routes, and then execute it
    $router->add('attendees_list', '/attendees')
        ->addValues(['controller' => 'attendees_controller']);
    $router->add('homepage', '{/name}')
        ->addValues(['controller' => 'homepage_controller']);

    return $router;
};
$c['logger_writer'] = function(Container $c) {
    return new Stream($c['log_path']);
};
$c['logger'] = function(Container $c) {
    $logger = new Logger();
    $logger->addWriter($c['logger_writer']);

    return $logger;
};

return $c;
