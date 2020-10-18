<?php

	$name = !empty($_POST['hay_name']);
	$email = !empty($_POST['hay_uname']);
	$pass = !empty($_POST['hay_pass']);
	$cpass = !empty($_POST['hay_cpass']);

	$append = "&error=please+fill+the+form+completely";

	if( $name && $email && $pass && $cpass ){
		$name = $_POST['hay_name'];
		$email = $_POST['hay_uname'];
		$pass = $_POST['hay_pass'];
		$cpass = $_POST['hay_cpass'];

		if( $cpass!=$pass ){
			$append = "&error=passwords+do+not+match";
		
		} else {

			$check = DB::table('user')
				->where( 'email', $email )
				->show();
			$check = end($check);

			if( !$check ){

				$check = DB::insert('user', array(
					'name' => $name,
					'email' => $email,
					'password' => md5($pass),
					'token' => strtolower(md5( microtime() ).md5( microtime() )),
					'created_at' => Date('Y-m-d H:i:s')
				));

				if( $check ){
					$append = "&success=account+created+successfully+you+can+now+login";
				}

			} else {
				$append = "&error=email+exists+already";
				unset($check);
			}
		}
		
		if( empty($check) ) $append .= "&hay_name=$name&hay_uname=$email";
	}

	header('Location: '.F::route('account/new?'.$append));