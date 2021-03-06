<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->group(['prefix' => 'api'], function ($router)
{
    $router->post('user/signup', 'AuthController@signup');
    $router->post('user/login', 'AuthController@login');

    $router->group(['middleware' => 'auth'], function ($router)
    {
        $router->post('transaction', 'TransactionController@store');
        $router->post('user/logout', 'AuthController@logout');
    });

});



