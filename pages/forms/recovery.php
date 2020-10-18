<?php

	if( isset($_POST['hay_uname']) ){
		$email = $_POST['hay_uname'];
		$check = DB::table('user')
			->where( 'email', $email )
			->show();

		$check = end($check);
		if( $check ){
			$time = time();
			$url = F::route('account/recovery').'/!/'.$time.'-'.$check['token'].'-'.md5(md5(md5($time)));
			$message = "You requested a password recovery please follow the link to proceed. <a href='$url'>$url</a>. Kindly note that this token would expired after 10 minutes.";
			$subject = "Haycube &raquo; Password Recovery";

			# Send Message
			die( $message );
			
			header('Location: '.F::route("account/recovery/?&success=We+just+sent+you+a+message,+please+check+your+email+to+complete+and+make+sure+to+check+the+spam+folder"));
		} else {
			header('Location: '.F::route("account/recovery/?&error=User+not+found&hay_uname=$email"));
		}
	}