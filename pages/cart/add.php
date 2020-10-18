<?php

	F::sessnoton('auth_user', F::route('account'));
	
	$GLOBALS['error'] = true;
	global $error;

	Page::child('{slug}', function($_ARGS){
		global $error;
		$item = DB::table('collections', '*', function(&$row){
			$loc = __BASE.'/uploads/'.$row['file'];
			if( !file_exists($loc) ){
				return FALSE;
			}
			$image = getimagesize($loc);
			#$row['size'] = "${image[0]} x ${image[1]}";
			#$row['area'] = $image[0] * $image[1];
			$row['src'] = "data:${image['mime']};base64,".base64_encode(file_get_contents($loc));
			$row['base_price'] = $row['price'];
			#$row['date'] = F::ago($row['date']);
		})
			->where(
				'slug', trim($_ARGS['slug'])
			)
			->show();
		$item = end($item);
		if( $item ){

			if( isset($_SESSION['auth_user']) ){
				$temp = DB::table( '`order`' )
					->where( 'user', $_SESSION['auth_user'] )
					->where( '+collection', $item['id'] )
					->show();
				$temp = end($temp);
				if( $temp && $temp['downloads'] > 0 ){
					die( Template::parse('output', array( 'title'=>'Add Cart', 'message'=>'You\'ve have this image in your collection already. <a href="'.F::route("account/collection").'">My Collections</a>' )) );
				}
			}

			if( !isset($_SESSION['cart']) ){
				$_SESSION['cart'] = [];
			}

			if( !Cart::check( $item['id'] ) ){
				$_SESSION['cart'][] = $item;
			}
			$error = false;
			header('Location: '.F::route('cart'));
		}
	});

	if( $error ) include __DIR__.'/../404.php';