<?php

require_once __DIR__.'/skosh/Foundation/Application.php';

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Skosh application instance
| which serves as the "glue" for all the components of Skosh.
|
*/

$app = new Skosh\Foundation\Application;

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can simply call the run method,
| which will execute the request.
|
*/

$app->run();