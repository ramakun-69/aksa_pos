<?php

if(!isset($routes))
{ 
    $routes = \Config\Services::routes(true);
}

$routes->group('stok_opname', ['filter' => 'auth', 'namespace' => 'App\Modules\StokOpname\Controllers'], function($routes){
	$routes->get('/', 'StokOpname::index', ['filter' => 'permit:viewStokOpname']);
});


$routes->group('api', ['filter' => 'jwtauth', 'namespace' => 'App\Modules\StokOpname\Controllers\Api'], function($routes){
    $routes->get('stok_opname', 'StokOpname::index', ['filter' => 'permit:viewStokOpname']);
	$routes->get('stok_opname/(:segment)', 'StokOpname::show/$1', ['filter' => 'permit:viewStokOpname']);
	$routes->post('stok_opname/save', 'StokOpname::create', ['filter' => 'permit:createStokOpname']);
	$routes->put('stok_opname/update/(:segment)', 'StokOpname::update/$1', ['filter' => 'permit:updateStokOpname']);
	$routes->delete('stok_opname/delete/(:segment)', 'StokOpname::delete/$1', ['filter' => 'permit:deleteStokOpname']);
});