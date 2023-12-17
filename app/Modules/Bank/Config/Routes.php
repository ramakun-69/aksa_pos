<?php

if(!isset($routes))
{ 
    $routes = \Config\Services::routes(true);
}

$routes->group('bank', ['filter' => 'auth', 'namespace' => 'App\Modules\Bank\Controllers'], function($routes){
	$routes->get('/', 'Bank::index', ['filter' => 'permit:viewBank']);
});


$routes->group('api', ['filter' => 'jwtauth', 'namespace' => 'App\Modules\Bank\Controllers\Api'], function($routes){
    $routes->get('bank', 'Bank::index', ['filter' => 'permit:viewBank']);
	$routes->get('bank/saldo', 'Bank::saldo', ['filter' => 'permit:viewBank']);
	$routes->get('bank/(:segment)', 'Bank::show/$1', ['filter' => 'permit:viewBank']);
	$routes->post('bank/save', 'Bank::create', ['filter' => 'permit:createBank']);
	$routes->put('bank/update/(:segment)', 'Bank::update/$1', ['filter' => 'permit:updateBank']);
	$routes->delete('bank/delete/(:segment)', 'Bank::delete/$1', ['filter' => 'permit:deleteBank']);

	$routes->get('bank/akun/all', 'BankAkun::index', ['filter' => 'permit:viewBankAkun']);
	$routes->get('bank/akun/(:segment)', 'BankAkun::show/$1', ['filter' => 'permit:viewBankAkun']);
	$routes->post('bank/akun/save', 'BankAkun::create', ['filter' => 'permit:createBankAkun']);
	$routes->put('bank/akun/update/(:segment)', 'BankAkun::update/$1', ['filter' => 'permit:updateBankAkun']);
	$routes->delete('bank/akun/delete/(:segment)', 'BankAkun::delete/$1', ['filter' => 'permit:deleteBankAkun']);
	$routes->put('bank/akun/setutama/(:segment)', 'BankAkun::setUtama/$1', ['filter' => 'permit:updateBankAkun']);
});