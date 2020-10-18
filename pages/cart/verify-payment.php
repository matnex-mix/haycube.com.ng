<?php

	define( 'PAYSTACK_SECRET', 'sk_test_e42a5ef95cfb6c42720d480048c086e8b1205368' );
	include(__BASE.'/payment/payment.php');

	$trxref = F::get('trxref');

	$check_ref = DB::table('payments')
		->where( 'tid', $trxref )
		->show();
	$check_ref = end( $check_ref );

	if( $check_ref ){
		die('Payment completed already...');
	}

	$try = PaymentApi::verify( $trxref );
	if( $try[0] ){

		$user = $try[1]->customer->email;
		$amount = Cart::total();

		if( preg_match('/^user-[a-z0-9]*@haycube.com.ng$/', $user) ){

			$user = preg_replace('/user-|@haycube.com.ng/', '', $user);
			$try_user = DB::table('user')
				->where( 'md5(sha1(id))', $user, '' )
				->show();
			$try_user = end($try_user);

			if( $try_user ){

				if( $amount == $try[1]->amount/100 ){

					$add_to_payments = DB::insert('payments', array(
						'user' => $try_user['id'],
						'tid' => $trxref,
						'amount' => $amount,
						'with' => "PAYSTACK",
						'date' => Date( 'Y-m-d H:i:s', strtotime( $try[1]->paid_at ) ),
					));

					if( $add_to_payments!==true ){
						die('An error ocurred, kindly bookmark this page and retry later on to confirm payment...');
					}

					foreach ( $_SESSION['cart'] as $value ) {
						DB::insert('order', array(
							'user' => $try_user['id'],
							'collection' => $value['id'],
							'downloads' => 5,
						));
					}

					unset($_SESSION['cart']);

				} else {
					$try[1]->status = 'error';
					$try[1]->gateway_response = 'amounts do not tally';
				}

			} else {
				$try[1]->status = 'error';
				$try[1]->gateway_response = 'invalid user';
			}

		} else {
			$try[1]->status = 'error';
			$try[1]->gateway_response = 'invalid transaction';
		}

	}

	if( isset($try[1]) && is_object($try[1]) ){
		
		Template::__('payment-summary', array(
			'data' => array(
				'ref' => $try[1]->reference,
				'date' => Date( 'Y-m-d H:i:s', strtotime( $try[1]->paid_at ) ),
				'email' => $try[1]->customer->email,
				'response' => $try[1]->gateway_response,
				'amount' => round( $try[1]->amount/100, 2 ),
				'status' => ( $try[1]->status=='success' ? true : false ),
			),
		));

	} else {
		die('An error ocurred, kindly bookmark this page and retry later on to confirm payment...');
	}