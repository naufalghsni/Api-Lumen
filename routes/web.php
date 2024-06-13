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

$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');
$router->get('/profile', 'AuthController@me');

//Stuff
$router->get('/stuffs','StuffController@index'); 

$router->group(['prefix' => 'stuff','middeware' => 'auth'], function() use ($router){
    //Static Routes
    // $router->get('/stuff', 'StuffController@index');
    $router->post('/store', 'StuffController@store');
    $router->get('/trash', 'StuffController@trash');

    //Dynamic routes
    $router->get('{id}', 'StuffController@show');
    $router->patch('/{id}', 'StuffController@update');
    $router->delete('/{id}', 'StuffController@delete');
    $router->get('/restore{id}', 'StuffController@restore');
    $router->delete('/permanent{id}', 'StuffController@deletPermanent');
});

// $router->post('/login', 'UserController@login');
// $router->get('/logout', 'UserController@logout');

$router->group(['prefix' => 'user'], function() use ($router) {
    // static routes : tetap
    $router->get('/', 'UserController@index');
    // $router->get('detail/{id}', 'UserController@index');
    // $router->patch('/update/{id}', 'UserController@index');
    // $router->get('delet/{id}', 'UserController@index');
    $router->post('/store', 'UserController@store');
    $router->get('/trash', 'UserController@trash');

    //dunamic routes : berubah - rubah
    $router->get('{id}', 'UserController@show');
    $router->patch('update/{id}', 'UserController@update');
    $router->delete('/{id}','UserController@destroy');
    $router->get('/restore/{id}', 'UserController@restore');
    $router->delete('/permanent/{id}', 'UserController@deletePermanent');
});
//inbound

$router->group(['prefix' => 'inbound-stuff/', 'middleware' => 'auth'], function () use ($router) {
    $router->get('/', 'InboundStuffController@index');
    $router->post('/store', 'InboundStuffController@store');
    $router->get('detail/{id}', 'InboundStuffController@show');
    $router->patch('update/{id}', 'InboundStuffController@update');
    $router->delete('delete/{id}', 'InboundStuffController@destroy');
    $router->get('recycle-bin', 'InboundStuffController@recycle-bin');
    $router->get('restore/{id}', 'InboundStuffController@restore');
    $router->get('force-delete/{id}', 'InboundStuffController@forceDestroy');
});
//Stuff-Stock
$router->group(['prefix' => 'stuff-stock', 'middleware' => 'auth'], function () use ($router) {
    $router->get('/', 'InboundStuffControler@index');
    $router->post('store', 'StuffStockController@store');
    $router->get('detail/{id}', 'StuffStockController@show');
    $router->patch('update/{id}', 'StuffStockController@update');
    $router->delete('delete/{id}', 'StuffStockController@destroy');
    $router->get('recycle-bin', 'StuffStockController@recycle-bin');
    $router->get('restore/{id}', 'StuffStockController@restore');
    $router->post('addstock/{id}', 'StuffStockController@addStock');
    $router->post('subStock/{id}', 'StuffStockController@subStock');
});

$router->group(['prefix' => 'lending/'], function () use ($router) {
    $router->get('/data', 'LendingController@index');
    $router->get('/lendings', 'LendingController@index');
    $router->post('/store', 'LendingController@store');
    $router->get('detail/{id}', 'LendingController@store');
    $router->patch('update/{id}', 'LendingController@update');
    $router->delete('delete/{id}', 'LendingController@destroy');
    $router->post('/lendings/store', 'LendingController@store');
    $router->get('/{id}', 'LendingController@show');
});

$router->group(['prefix' => 'restoration/'], function () use ($router) {
    $router->get('/data', 'RestorationController@index');
});