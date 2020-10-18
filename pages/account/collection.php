<?php

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
		->show();

	Template::__('my-collection', array( 'data'=>$my_c ));