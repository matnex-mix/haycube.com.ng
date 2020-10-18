<?php

	if( isset($_POST['__init__']) ){
		$GLOBALS['errors'] = array();
		$name = $_POST['name'];
		$email = $_POST['email'];
		$group = $_POST['group'];
		$subject = $_POST['subject'];
		$message = $_POST['message'];

		global $errors;

		if( checkErrors($name, $email, $group, $subject, $message) ){
			$composed = "<h3>$subject</h3><hr/><p>$message</p><hr/>Posted in <b>$group</b> By <i>$name</i>, $email";
			$subject = 'Website Message: '.$subject;

			// send composed message
			header('Location: '.F::route('contact?success='.urlencode('message sent successfully')));
		} else {
			$name = urlencode($name);
			$email = urlencode($email);
			$group = urlencode($group);
			$subject = urlencode($subject);
			$message = urlencode($message);
			$errors = urlencode(implode('::::', $errors));
			header('Location: '.F::route("contact?&name=$name&email=$email&group=$group&subject=$subject&message=$message&error=$errors"));
		}
	}

	function checkErrors($name, $email, $group, $subject, $message) {
		global $errors;
		if( $name==''||strlen($name)>20 ){
			$errors[] = 'Please supply a genuine name';
		}
		if( $email==''||strlen($name)>50 ){
			$errors[] = 'Please supply a genuine email address';
		}
		if( $subject==''||strlen($subject)>230 ){
			$errors[] = 'supply a subject not more than 200 characters';
		}
		if( $message==''||strlen($message)>1100 ){
			$errors[] = 'supply a message not more than 1000 characters';
		}

		return !boolval(sizeof($errors));
	}