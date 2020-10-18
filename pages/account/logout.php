<?php

	F::sessnoton( 'auth_user', F::route('account') );

	session_unset();
	session_destroy();

	header('Location: '.F::route('account/login'));