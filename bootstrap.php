<?php

require __DIR__.'/vendor/autoload.php';

use Pimple\Container;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
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

    // merge the matched attributes back into Symfony's request
    if ($route) {
        $c['request']->attributes->add($route->params);
    }

    // get the "controller" out, or default to error404_controller
    $controller = $c['request']->attributes->get('controller', 'error404_controller');

    if ($controller == 'error404_controller') {
        $msg = sprintf('Controller not found for "%s"', $c['request']->getPathInfo());
        $c['logger']->err($msg);
    } else {
        $c['logger']->info(sprintf('Found controller "%s"', $controller));
    }

    // execute the controller and get the response
    $response = call_user_func_array($controller, array($c['request'], $c));
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
