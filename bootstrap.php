<?php

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/*
 * *************** Our one-method framework
 */

function _run_app(Request $request) {
    $c = require 'services.php';

    // include the routing and controllers
    require 'routing.php';
    require_once 'controllers.php';

    // run the framework!
    $route = $c['router']->match(
        $request->getPathInfo(),
        $request->server->all()
    );

    // merge the matched attributes back into Symfony's request
    if ($route) {
        $request->attributes->add($route->params);
    }

    // get the "controller" out, or default to error404_controller
    $controller = $request->attributes->get('controller', 'error404_controller');

    if ($controller == 'error404_controller') {
        $msg = sprintf('Controller not found for "%s"', $request->getPathInfo());
        $c['logger']->err($msg);
    } else {
        $c['logger']->info(sprintf('Found controller "%s"', $controller));
    }

    // execute the controller and get the response
    $response = call_user_func_array($controller, array($request, $c));
    if (!$response instanceof Response) {
        throw new Exception(sprintf('Your controller "%s" did not return a response!!', $controller));
    }

    return $response;
}
