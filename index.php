<?php

use Symfony\Component\HttpFoundation\Request;

require __DIR__.'/bootstrap.php';

$request = Request::createFromGlobals();
$response = _run_app($request);
$response->send();
