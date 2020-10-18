<?php

	$my_p = DB::table('payments', '*', function(&$row){
		$row['amount'] = round(Currency::i($row['amount']), 2);
	})
		->where( 'user', $_SESSION['auth_user'] )
		->show();

	Template::__('payments', array( 'payments'=>$my_p ));