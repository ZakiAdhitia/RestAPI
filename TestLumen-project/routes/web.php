<?php

$router->get('/ ', function () use ($router) {
    return $router->app->version();
});

$router->group(['middleware' => 'cors'], function ($router) {

    
    $router->post('/login', 'AuthController@login');
    $router->get('/logout', 'AuthController@logout');
    $router->get('/profile', 'AuthController@me');
    
    
    $router->group(['prefix' => 'stuff/' , 'middleware' => 'auth'], function() use ($router) {
        
        $router->get('/', 'StuffController@index');
        $router->post('/store', 'StuffController@store');
        $router->get('/trash', 'StuffController@trash');
        
        $router->get('show/{id}', 'StuffController@show');
        $router->patch('/{id}', 'StuffController@update');
        $router->delete('delete/{id}', 'StuffController@destroy');
        $router->get('/restore/{id}', 'StuffController@restore');
        $router->delete('/permanent/{id}', 'StuffController@deletePermanent');
        $router->get('/stuff-options', 'StuffController@getStuffOptions');
        
    });
    
    $router->group(['prefix' => 'user/', 'middleware' => 'auth'], function() use ($router) {
        $router->post('store/', 'UserController@store');
        $router->get('/', 'UserController@index');
        $router->get('restore/{id}', 'UserController@restore');
        $router->delete('delete/{id}', 'UserController@destroy');
        $router->delete('permanent/{id}', 'UserController@forcedestroy');
        $router->patch('update/{id}', 'UserController@update');
        $router->get('show/{id}', 'UserController@show');
        $router->get('/trash', 'UserController@trash');
    });
    
    $router->group(['prefix' => 'inbound-stuff/', 'middleware' => 'auth'], function() use ($router){
        $router->get('/', 'InboundStuffController@index');
        $router->post('/store', 'InboundStuffController@store');    
        $router->delete('permanent/{id}', 'InboundStuffController@deletePermanent');
        $router->delete('delete/{id}', 'InboundStuffController@destroy');
        $router->get('login/', 'InboundStuffController@destroy');
        $router->get('restore/{id}', 'InboundStuffController@restore');
        $router->patch('update/{id}', 'InboundStuffController@update');
        $router->get('show/{id}', 'InboundStuffController@show');
    });
    
    $router->group(['prefix' => 'stuff-stock/', 'middleware' => 'auth'], function() use ($router){
        $router->post('store', 'StuffStockController@store');
        $router->post('update/{id}', 'StuffStockController@update');
    });
    
    
    $router->group(['prefix' => 'lending/'], function() use ($router){
        $router->get('/', 'LendingController@index');
        $router->get('/show', 'LendingController@show');
        $router->post('store', 'LendingController@store');
        $router->patch('/update/{id}', 'LendingController@update');
        $router->delete('delete/{id}', 'LendingController@destroy');
        $router->get('/trash', 'LendingController@trash');
        $router->delete('/permanent/{id}', 'LendingController@deletePermanent');
        $router->get('/restore/{id}', 'LendingController@restore');
        
        
    });
    
    
    $router->group(['prefix' => 'restoration/'], function() use ($router){
        $router->post('store', 'RestorationController@store');
        
    });
    
    
});
    
    