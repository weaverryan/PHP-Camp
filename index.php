<?php
require __DIR__.'/bootstrap.php';

try {
    $dbPath = __DIR__.'/data/database.sqlite';
    $dbh = new PDO('sqlite:'.$dbPath);
} catch(PDOException $e) {
    die('Panic! '.$e->getMessage());
}

// create a request object to help us
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Aura\Router\RouterFactory;

$request = Request::createFromGlobals();
$uri = $request->getPathInfo();

$routerFactory = new RouterFactory();
$router = $routerFactory->newInstance();

// create a router, build the routes, and then execute it
$router->add('attendees_list', '/attendees')
    ->addValues(['controller' => 'attendees_controller']);
$router->add('homepage', '{/name}')
    ->addValues(['controller' => 'homepage_controller']);
$route = $router->match($uri, $request->server->all());

// merge the matched attributes back into Symfony's request
if ($route) {
    $request->attributes->add($route->params);
}

// get the "controller" out, or default to error404_controller
$controller = $request->attributes->get('controller', 'error404_controller');

// execute the controller and get the response
$response = call_user_func_array($controller, array($request));
if (!$response instanceof Response) {
    throw new Exception(sprintf('Your controller "%s" did not return a response!!', $controller));
}

$response->send();

/*
 * My Controllers!
 */
function homepage_controller(Request $request) {

    $content = '<h1>PHP Camp!</h1>';
    $content .= '<a href="/attendees">See the attendees</a>';
    if ($name = $request->attributes->get('name')) {
        $content .= sprintf('<p>Oh, and hello %s!</p>', $name);
    }

    return new Response($content);
}

function attendees_controller(Request $request) {
    global $dbh;

    $sql = 'SELECT * FROM php_camp';
    $content = '<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" />';
    $content .= '<h1>PHP Camp Attendees</h1>';
    $content .= '<table class="table" style="width: 300px;">';
    foreach ($dbh->query($sql) as $row) {
        $content .= sprintf(
            '<tr><td style="font-size: 24px;">%s</td><td><img src="%s" height="120" /></td></tr>',
            $row['attendee'],
            $row['avatar_url']
        );
    }
    $content .= '</table>';

    return new Response($content);
}

function error404_controller(Request $request) {
    $content = '<h1>404 Page not Found</h1>';
    $content .= '<p>Find a boy (or girl) scout - they can fix this!</p>';

    $response = new Response($content);
    $response->setStatusCode(404);

    return $response;
}

