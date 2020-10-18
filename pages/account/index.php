<?php

	if( isset($_SESSION['auth_user']) ) {
		header('Location: '.F::route('account/dashboard'));
	} else if( isset($_COOKIE['hay_token']) ){

		$token_check = DB::table('user')
			->where( 'token', $_COOKIE['hay_token'] )
			->show();
		$token_check = end($token_check);

		if( $token_check ){
			$_SESSION['auth_user'] = $token_check['id'];
			header('Location: '.F::route('account/dashboard'));
		} else {
			header('Location: '.F::route('account/login'));
		}

	} else {
		header('Location: '.F::route('account/login'));
	}