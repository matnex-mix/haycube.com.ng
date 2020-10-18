<?php

F::sesson( 'admin', F::route( 'admin' ) );

Template::__('login', array(
	'admin' => F::route('admin/admin?a=login'),
));