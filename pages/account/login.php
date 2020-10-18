<?php

	F::sesson( 'auth_user', F::route('account/dashboard') );

	Template::__('login', array(
		'admin' => 0,
	));