<?php

	if( isset($_POST['__init__']) ){
		unset($_POST['__init__']);
		foreach ($_POST as $key => $value) {
			if( $value=='' ){
				unset($_POST[$key]);
			}
		}

		$append = '?';
		if( DB::update( 'user', $_POST )
			->where( 'id', $_SESSION['auth_user'] )
			->run() === TRUE ){
			$append .= "&message=Profile+updated+successfully";
		} else {
			$append .= "&error=An+error+ocurred";
		}

		header('Location: '.F::route('account/dashboard'.$append));
	}