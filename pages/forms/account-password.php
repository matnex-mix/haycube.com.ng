<?php

	if( isset($_POST['__init__']) ){
		global $__DBINSTANCE;
		
		$old = $_POST['old'];
		$new = $_POST['new'];

		$append = '?';
		DB::update('user', array(
			'password' => md5($new)
		))
			->where( 'id', $_SESSION['auth_user'] )
			->where( '+password', md5($old) )
			->run();

		if( $__DBINSTANCE->affected_rows > 0 ){
			$append .= "&message=Password+updated+successfully";
		} else {
			$append .= "&error=Wrong+old+password";
		}

		header('Location: '.F::route('account/dashboard'.$append));
	}