<?php
	$route = F::route('');

	if( isset($_GET['__init__']) ){
		unset($_GET['__init__']);
		$_SESSION['search_options'] = $_GET;
		if( $_GET['keyword'] ){
			$route .= '?k='.$_GET['keyword'];
		}
	} elseif (isset($_GET['clear'])) {
		unset($_SESSION['search_options']);
	}

	header("Location: $route");