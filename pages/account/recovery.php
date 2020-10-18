<?php

	F::sesson( 'auth_user', F::route('account/dashboard') );
	
	if( isset($_POST['new']) && isset($_POST['cnew']) && isset($_SESSION['recovery_user']) ){
		$new = $_POST['new'];
		$cnew = $_POST['cnew'];

		if( $new==$cnew ){
			DB::update('user', array(
				'password'=>md5($new)
			))
				->where( 'id', $_SESSION['recovery_user'] )
				->run();
				
			unset( $_SESSION['recovery_user'] );
			die( 'Password Changed Successfully<script>setTimeout(function(){ location.href=("'.F::route('account/login').'"); }, 700);</script>' );
		}
	}

	Page::child('{token}', function($_ARGS){

		$token = explode( '-', $_ARGS['token'] );

		if( md5(md5(md5($token[0])))!=$token[2] ){
			die( 'Invalid Token' );
		} else if( time()-$token[0] > 600 ) {
			die( 'Token Expired Already' );
		}

		$check = DB::table('user')
			->where( 'token', $token[1] )
			->show();
		$check = end($check);

		if( !$check ){
			die( 'Invalid Token' );
		}

		$_SESSION['recovery_user'] = $check['id'];

		die( Template::parse( 'password-change' ) );
	});

	Template::__( 'recovery' );