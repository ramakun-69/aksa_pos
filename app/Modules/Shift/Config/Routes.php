<?php

if(!isset($routes))
{ 
    $routes = \Config\Services::routes(true);
}

$routes->group('openclose_cashier', ['filter' => 'auth', 'namespace' => 'App\Modules\Shift\Controllers'], function($routes){
	$routes->get('/', 'ShiftOpenClose::index');
    $routes->get('print_html', 'ShiftOpenClose::printReportHtml');
});

$routes->group('openapi', ['namespace' => 'App\Modules\Shift\Controllers\Api'], function($routes){
    $routes->get('openclosecashier/status', 'ShiftOpenClose::getStatus');
});

$routes->group('api', ['filter' => 'jwtauth', 'namespace' => 'App\Modules\Shift\Controllers\Api'], function($routes){
    $routes->get('shift', 'Shift::index');
    $routes->get('shift/active', 'Shift::active');
    $routes->get('shift/(:segment)', 'Shift::show/$1');
	$routes->put('shift/update/(:segment)', 'Shift::update/$1', ['filter' => 'permit:updateShift']);

    $routes->get('openclosecashier', 'ShiftOpenClose::index');
    $routes->get('openclosecashier/(:segment)', 'ShiftOpenClose::show/$1');
    $routes->get('openclosecashier/detail/(:segment)', 'ShiftOpenClose::detail/$1');
    $routes->post('openclosecashier/save', 'ShiftOpenClose::create');
    $routes->put('openclosecashier/update/(:segment)', 'ShiftOpenClose::update/$1');
	$routes->delete('openclosecashier/delete/(:segment)', 'ShiftOpenClose::delete/$1', ['filter' => 'permit:deleteShift']);
    $routes->get('openclosecashier/find/getopen', 'ShiftOpenClose::getOpen');
    $routes->get('openclosecashier/get/reports', 'ShiftOpenClose::laporanOpenClose');

});