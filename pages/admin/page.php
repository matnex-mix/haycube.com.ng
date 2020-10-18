<?php

F::sessnoton( 'admin', F::route('admin/login') );

include 'pages/admins.php';
include 'pages/users.php';
include 'pages/orders.php';
include 'pages/currency.php';
include 'pages/pictures.php';

Page::children(array(

	'admins/add' => 'Admins@add',
	'admins/delete/{id}' => 'Admins@delete',
	'admins/edit/{id}' => 'Admins@edit',
	'admins' => 'Admins@index',

	'users' => 'Users@index',

	'orders' => 'Orders@index',

	'currency/edit/{id}' => 'CurrencyPage@edit',
	'currency/add' => 'CurrencyPage@add',
	'currency' => 'CurrencyPage@index',

	'pictures/delete/{id}' => 'Pictures@delete',
	'pictures/edit/{id}' => 'Pictures@edit',
	'pictures/add' => 'Pictures@add',
	'pictures' => 'Pictures@index',

));