<?php

/*
 * Extend SPhp Flexibly
 *
 *
*/

function searchAndFilter( $k, $page=0 ){
	include 'extends/extrafilter.php';

	$GLOBALS['min_price'] = 0;
	$GLOBALS['max_price'] = 0;
	$GLOBALS['min_discount'] = 0;
	$GLOBALS['max_discount'] = 0;
	$GLOBALS['options'] = array();
	$GLOBALS['ex_filter'] = array();
	global $min_price, $max_price, $min_discount, $max_discount, $options, $ex_filter;

	$per_page = 28;
	$sort = 'title';
	$order = '';

	if( isset($_SESSION['search_options']) ){
		$options = $_SESSION['search_options'];
	}
	if( isset($options['per_page']) && $options['per_page'] ){
		$per_page = $options['per_page'];
	}
	if( isset($options['sort']) && $options['sort'] ){
		$sort = $options['sort'];
		if( $sort=='random' ){
			$sort = '';
			$order = 'RAND()';
		} elseif ( $sort=='size' ) {
			$sort = 'area';
		}
	}
	if( isset($options['min_pricing']) && $options['min_pricing'] ){
		$min_price = Currency::i( $options['min_pricing'] );
	}
	if( isset($options['max_pricing']) && $options['max_pricing'] ){
		$max_price = Currency::i( $options['max_pricing'] );
	}
	if( isset($options['discount']) && $options['discount'] ){
		$min_discount = $options['discount'];
		$discount = explode('-', $min_discount);
		if( sizeof($discount)>1 ){
			$min_discount = $discount[0];
			$max_discount = $discount[1];
		}
	}

	$ex_filter = array_intersect_key($options, extraFilters());

	$images = DB::table('collections', '*', function( &$row ){
			global $min_price, $max_price, $min_discount, $max_discount, $options, $ex_filter;
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

			$discount_rule = ($row['discount']<$min_discount || ( $max_discount>$min_discount && $row['discount']>$max_discount ));
			$price_rule = ($row['price']<$min_price || ( $max_price>$min_price && $row['price']>$max_price ));
			if( $price_rule || $discount_rule ){
				return FALSE;
			}


			foreach ($ex_filter as $name=>$k) {
				if( !ExtraFilter::$name( $image, $row ) ){
					return FALSE;
				}
			}

			if( !empty($options['size_icon']) && in_array($row['size'], getSizes()) ){

			} else if( !empty($options['size_small']) && checkSize($row['size'], 'w>=180,h>=240,w<350,h<500') ){

			} else if( !empty($options['size_medium']) && checkSize($row['size'], 'w>=350,h>=500,w<768,h<1024') ){

			} else if( !empty($options['size_large']) && checkSize($row['size'], 'w>=768,h>=1024') ){

			} else if( !empty($options['size_cx']) && checkSize($row['size'], $options['size_cx']) ){

			} else if( isset($options['size_icon']) || isset($options['size_small']) || isset($options['size_medium']) || isset($options['size_large']) || !empty($options['size_cx']) ) {
				return FALSE;
			}
		})
			->where( 'title', "*%$k%" )
			->order( $sort, $order )
			->show( $page*$per_page, $per_page );

	return $images;
}

function getSizes(){
	$sizes = [ '16 x 16', '24 x 24', '32 x 32', '48 x 48', '256 x 256', '64 x 64', '128 x 128', '512 x 512', '1024 x 1024', '96 x 96', '29 x 29', '50 x 50', '57 x 57', '58 x 58', '72 x 72', '100 x 100', '114 x 114', '144 x 144', '40 x 40', '60 x 60', '76 x 76', '80 x 80', '120 x 120', '152 x 152', '180 x 180', '192 x 192', '62 x 62', '99 x 99', '173 x 173', '200 x 200' ];
	return $sizes;
}

function extraFilters(){
	return array( 'ex_gif'=>'GIF', 'ex_jpeg'=>'JPEG', 'ex_png'=>'PNG', 'ex_cartoon'=>'Cartoon', 'ex_background'=>'Background' );
}

function checkSize( $size, $check ){
	$size = explode(' x ', $size);
	$w = $size[0]; $h = $size[1];
	$check = explode(',', $check);
	
	foreach ($check as $value) {
		$value = preg_replace('/&lt;/', "<", $value);
		$value = preg_replace('/&gt;/', ">", $value);
		
		try {
			if( !eval("return $$value;") ){
				return false;
			}
		} catch( \Throwable $e ){
			return false;
		}
	}

	return true;
}

class Currency {
	public function all(){
		if( !isset($_SESSION['currency']) ){
			$_SESSION['currency'] = 2;
		}
		return DB::table('currency')->show();
	}

	public function current(){
		if( !isset($_SESSION['currency']) ){
			$_SESSION['currency'] = 2;
		}
		return $_SESSION['currency'];
	}

	public function sym(){
		$rate = DB::table('currency')
			->where(
				'id', Currency::current()
			)
			->show();
		return end($rate)['symbol'];
	}

	public function abbr(){
		$rate = DB::table('currency')
			->where(
				'id', Currency::current()
			)
			->show();
		return end($rate)['abbr'];
	}

	public function i( $amount ){
		$rate = DB::table('currency')
			->where(
				'id', Currency::current()
			)
			->show();
		$rate = end($rate)['rate'];

		return doubleval($amount)/doubleval($rate);
	}
}
class Cart{
	public function total(){
		$total = 0;
		if( isset($_SESSION['cart']) ){
			foreach ($_SESSION['cart'] as $i) {
				$total += $i['price'];
			}
		}
		return $total;
	}
	public function check( $id ){
		if( isset($_SESSION['cart']) ){
			foreach ($_SESSION['cart'] as $i) {
				if( $i['id'] == $id ){
					return true;
				}
			}
		}
		return false;
	}
}