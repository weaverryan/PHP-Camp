<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Pimple\Container;

/*
 * Define our controllers
 */

function homepage_controller(Request $request) {

    $content = '<h1>PHP Camp!</h1>';
    $content .= '<a href="/attendees">See the attendees</a>';
    if ($name = $request->attributes->get('name')) {
        $content .= sprintf('<p>Oh, and hello %s!</p>', $name);
    }

    return new Response($content);
}

function attendees_controller(Request $request, Container $c) {
    $dbh = $c['connection'];

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
