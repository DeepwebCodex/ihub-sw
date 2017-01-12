<?php
// Here you can initialize variables that will be available to your tests

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->loadEnvironmentFrom('.env.testing');
$app->instance('request', new \Illuminate\Http\Request);
$app->make('Illuminate\Contracts\Http\Kernel')->bootstrap();