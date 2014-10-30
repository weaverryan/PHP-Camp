<?php

$c = require __DIR__.'/bootstrap.php';
require 'routing.php';
require 'controllers.php';

$response = _run_app($c);
$response->send();
