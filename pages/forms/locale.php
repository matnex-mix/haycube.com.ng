<?php

	if( isset($_GET['language']) ){
		$_SESSION['language'] = $_GET['language'];
	}

	if( isset($_GET['currency']) ){
		$_SESSION['currency'] = $_GET['currency'];
	}

	$proceed = '';
	if( isset($_GET['proceed']) ){
		$proceed = $_GET['proceed'];
	}
	header('Location: '.$proceed);