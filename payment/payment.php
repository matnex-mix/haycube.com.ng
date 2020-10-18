<?php

require('autoload.php');

use Yabacon\Paystack;
use Yabacon\Paystack\MetadataBuilder;

if( !defined('PAYSTACK_SECRET') ){
	define( 'PAYSTACK_SECRET', 'sk_test_badb30a68e7aa6913d8c6d4d7dda2b765f4c785c' );
}

class PaymentApi {
	
	function init( $array ){

		if( !empty($array) ){

			$array['amount'] *= 100;
			$meta = new MetadataBuilder();
			$func = function(){ return true; };

			if( !empty($array['callback_func']) ){
				$func = $array['callback_func'];
				unset( $array['callback_func'] );
			}
			
			if( !empty($array['metadata']) ){
				foreach ($array['metadata'] as $key => $value) {
					$meta->withCustomField( $key, $value );
				}
			}

			$meta = $meta->build();
			try{
		    
		        $paystack = new Paystack(PAYSTACK_SECRET);
		        $paystack->disableFileGetContentsFallback();

		        $trx = $paystack->transaction->initialize( $array );

		        if( $trx->status ){
		        	if( $func( $trx->data ) ){
		        		header( 'Location: '.$trx->data->authorization_url );
		        	}
		        }

		    } catch(Exception $e){
		    	print_r( $e->getMessage() );
		        return false;
		    }

		    return $trx->data->authorization_url;

		}

	}

	function verify( $ref_code ){

		try{

	        $paystack = new Paystack(PAYSTACK_SECRET);
	        $paystack->disableFileGetContentsFallback();

	        $trx = $paystack->transaction->verify([
	            'reference' => $ref_code,
	        ]);

		} catch(Exception $e){
		    print_r( $e->getMessage() );
		    return false;
		}

		$status = $trx->data->status === 'success';
		if( $status ){
			return [ true, $trx->data ];
		} else {
			return [ false, $trx->data ];
		}

	}

}