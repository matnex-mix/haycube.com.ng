<?php

	#session_unset();
	if( !isset($_SESSION['cart']) ){
		$_SESSION['cart'] = [];
	}

	foreach ($_SESSION['cart'] as $i => $row) {
		$row['oprice'] = round(Currency::i( $row['base_price'] ), 2);
		$row['price'] = $row['base_price'];
		$row['price'] -= ($row['price']*$row['discount'])/100;
		$row['price'] = round(Currency::i( $row['price'] ), 2 );

		$_SESSION['cart'][$i] = $row;
	}

	Template::__('cart', array( 'items' => $_SESSION['cart'] ));