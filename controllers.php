<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Pimple\Container;

/*
 * Define our controllers
 */

function homepage_controller(Request $request, Container $c) {
    $content = $c['twig']->render('homepage.twig', array(
        'name' => $request->attributes->get('name'),
    ));

    return new Response($content);
}

function attendees_controller(Request $request, Container $c) {
    $dbh = $c['connection'];

    $campers = $dbh->query('SELECT * FROM php_camp');

    $content = $c['twig']->render('attendees.twig', array(
        'campers' => $campers
    ));

    return new Response($content);
}

function error404_controller(Request $request, Container $c) {
    $content = $c['twig']->render('error404.twig');

    $response = new Response($content);
    $response->setStatusCode(404);

    return $response;
}
