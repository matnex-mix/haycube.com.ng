<?php
	$GLOBALS['_404'] = true;
	global $_404;

	Page::child('{slug}', function( $_ARGS ){
		global $_404;
		$item = DB::table('collections', '*', function(&$row){
			$loc = __BASE.'/uploads/'.$row['file'];
			if( !file_exists($loc) ){
				return FALSE;
			}
			$image = getimagesize($loc);
			$row['size'] = "${image[0]} x ${image[1]}";
			$row['area'] = $image[0] * $image[1];
			$row['src'] = "data:${image['mime']};base64,".base64_encode(file_get_contents($loc));
			$row['oprice'] = round(Currency::i( $row['price'] ), 2);
			$row['price'] -= ($row['price']*$row['discount'])/100;
			$row['price'] = round(Currency::i( $row['price'] ), 2 );
			$row['date'] = F::ago($row['date']);
		})
			->where(
				'slug', trim($_ARGS['slug'])
			)
			->show();
		$item = end($item);
		if( $item ){

			$item['download'] = 0;
			if( isset($_SESSION['auth_user']) ){
				$temp = DB::table( '`order`' )
					->where( 'user', $_SESSION['auth_user'] )
					->where( '+collection', $item['id'] )
					->show();
				$temp = end($temp);
				$item['download'] = 0;
				if( $temp && $temp['downloads'] > 0 ){
					$item['download'] = 1;
				}
			}

			$item['in_cart'] = (int)Cart::check($item['id']);

			Template::__('items', array( 'data'=>$item ));
		}
		$_404 = false;
	});

	if( $_404 ) include '404.php';