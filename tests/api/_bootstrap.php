<?php
// Here you can initialize variables that will be available to your tests

use App\Components\ExternalServices\RemoteSession;

require 'bootstrap/autoload.php';
$app = require 'bootstrap/app.php';
$app->loadEnvironmentFrom('.env.testing');
$app->instance('request', new \Illuminate\Http\Request);
$app->make('Illuminate\Contracts\Http\Kernel')->bootstrap();

/*$app->bind('RemoteSession', function(){
    $remote_session = Mockery::mock(RemoteSession::class);

    $remote_session->shouldReceive('start')->andReturnSelf();
    $remote_session->shouldReceive('get')->andReturn(112455);

    return $remote_session;
});*/