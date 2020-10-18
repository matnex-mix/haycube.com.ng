<?php

	if( !isset($_GET['name']) ){
		$_GET['name'] = '';
	}

	if( !isset($_GET['email']) ){
		$_GET['email'] = '';
	}

	if( !isset($_GET['group']) ){
		$_GET['group'] = '';
	}

	if( !isset($_GET['subject']) ){
		$_GET['subject'] = '';
	}

	if( !isset($_GET['message']) ){
		$_GET['message'] = '';
	}

	if( !isset($_GET['success']) ){
		$_GET['success'] = '';
	}

	if( !isset($_GET['error']) ){
		$_GET['error'] = array();
	} else {
		$_GET['error'] = explode('::::', $_GET['error']);
	}

	Template::__('contact', $_GET);