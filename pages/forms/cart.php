<?php

	if( !empty($_POST['action']) ){
		$action = $_POST['action']; unset($_POST['action']);
		$cart = $_SESSION['cart'];
		if( $action=='r' ){
			foreach ($_POST as $key => $value) {
				$key = str_replace('item_', '', $key);
				unset($cart[$key]);
			}
		}
		$_SESSION['cart'] = $cart;
		header('Location: '.F::route('cart'));
	}