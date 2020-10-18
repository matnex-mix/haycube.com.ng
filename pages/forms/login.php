<?php

	if( isset($_POST['__init__']) ){
		$uname = $_POST['hay_uname'];
		$pass = $_POST['hay_pass'];
		$auto_log = $_POST['hay_me'];

		$query = "&hay_uname=$uname&hay_me=$auto_log";
		$append = "&error=Invalid+username+or+password";

		$check = DB::table('user')
			->where( 'email', $uname )
			->where( 'password', md5($pass) )
			->show();
		$check = end($check);

		if( $check ){
			$_SESSION['auth_user'] = $check['id'];

			if( $auto_log==1 ){
				setcookie( 'hay_token', $check['token'], time() + (86400*30), '/' );
			}
			
			header('Location: '.F::route('account'));
			die('');
		}

		header('Location: '.F::route('account/login?'.$query.$append));
	} else {
		header('Location: '.F::route('account'));
	}