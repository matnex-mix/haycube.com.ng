<?php
	include 'includes/autoload.php';
	include 'extender.php';

	use SPhp\Framework\Framework;
	use SPhp\Framework\Analytics;
	use SPhp\Framework\Response;
	use SPhp\Framework\Recovery;

	$GLOBALS['Response'] = new Response();
	global $Response;
	session_start();

	Framework::boot( __FILE__ );
	Framework::secure();
	DB::connect();
	Recovery::check();
	Framework::crossURLMessages();
	
	include 'global.php';

	Framework::manage( $_SERVER['REQUEST_URI'] );
	$Response->show();
	Analytics::push();
	DB::close();