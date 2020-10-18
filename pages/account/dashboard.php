<?php

	F::sessnoton('auth_user', F::route('account/login'));

	$account = DB::table('user')
		->where( 'id', $_SESSION['auth_user'] )
		->show();
	$account = end($account);

	if( !$account ){
		header('Location: '.F::route('account/logout'));
	}

	$my_c = DB::table('`order`', '*', function(&$row){
		$c_o = DB::table('collections')
			->where( 'id', $row['collection'] )
			->show();
		if(!sizeof($c_o)){
			return FALSE;
		}
		$c_o = end($c_o);
		unset($c_o['id']);
		$row = array_merge($row, $c_o);
		$loc = __BASE.'/uploads/'.$row['file'];
		if( !file_exists($loc) ){
			return FALSE;
		}
		$image = getimagesize($loc);
		$row['src'] = "data:${image['mime']};base64,".base64_encode(file_get_contents($loc));
	})
		->where( 'user', $_SESSION['auth_user'] )
		->show( 0, 4 );

	$my_p = DB::table('payments', '*', function(&$row){
		$row['amount'] = round(Currency::i($row['amount']), 2);
		$row['date'] = explode(' ', $row['date'])[0];
	})
		->where( 'user', $_SESSION['auth_user'] )
		->show( 0, 28 );

	Template::__('dashboard', array( 'user'=>$account, 'my_collections'=>$my_c, 'payments'=>$my_p ));