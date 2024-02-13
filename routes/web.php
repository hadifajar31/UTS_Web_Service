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

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get('users', 'UsersController@index');
$router->get('users/{id}', 'UsersController@show');
$router->get('products', 'ProductsController@index');
$router->get('products/{id}', 'ProductsController@show');
$router->post('users/register', 'UsersController@register');
$router->post('users/login', 'UsersController@login');
$router->post('users/logout', 'UsersController@logout');

$router->group(['middleware' => 'auth'], function($router) {
    $router->put('users/{id}', 'UsersController@update');
    $router->delete('users/{id}', 'UsersController@delete');

    $router->post('products', 'ProductsController@store');
    $router->put('products/{id}', 'ProductsController@update');
    $router->delete('products/{id}', 'ProductsController@delete');
});
