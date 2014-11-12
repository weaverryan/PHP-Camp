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
$request = Request::createFromGlobals();

$uri = $request->getPathInfo();

if ($uri == '/' || $uri == '') {

    $content = '<h1>PHP Camp!</h1>';
    $content .= '<a href="/attendees">See the attendees</a>';
    if ($name = $request->query->get('name')) {
        $content .= sprintf('<p>Oh, and hello %s!</p>', $name);
    }

    $response = new Response($content);

} elseif ($uri == '/attendees') {

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

    $response = new Response($content);

} else {
    $content = '<h1>404 Page not Found</h1>';
    $content .= '<p>Find a boy (or girl) scout - they can fix this!</p>';

    $response = new Response($content);
    $response->setStatusCode(404);
}

$response->send();
