<?php

if(!isset($routes))
{ 
    $routes = \Config\Services::routes(true);
}

$routes->group('stok', ['filter' => 'auth', 'namespace' => 'App\Modules\StokInOut\Controllers'], function($routes){
	$routes->get('/', 'Stok::index', ['filter' => 'permit:viewStokInOut']);
});


$routes->group('api', ['filter' => 'jwtauth', 'namespace' => 'App\Modules\StokInOut\Controllers\Api'], function($routes){
    $routes->get('stok', 'Stok::index', ['filter' => 'permit:viewStokInOut']);
	$routes->get('stok/(:segment)', 'Stok::show/$1', ['filter' => 'permit:viewStokInOut']);
	$routes->post('stok/save', 'Stok::create', ['filter' => 'permit:createStokInOut']);
	$routes->put('stok/update/(:segment)', 'Stok::update/$1', ['filter' => 'permit:updateStokInOut']);
	$routes->delete('stok/delete/(:segment)', 'Stok::delete/$1', ['filter' => 'permit:deleteStokInOut']);
});