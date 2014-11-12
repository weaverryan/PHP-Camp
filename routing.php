<?php

/*
 * Define our Routes
 */
/** @var \Aura\Router\Router|\Aura\Router\RouteCollection $router */
$router = $c['router'];

$router->add('attendees_list', '/attendees')
    ->addValues(['controller' => 'attendees_controller']);
$router->add('homepage', '{/name}')
    ->addValues(['controller' => 'homepage_controller']);
