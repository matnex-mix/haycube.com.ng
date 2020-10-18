<?php

namespace SPhp\Framework;

class Functions {
	public function static( $path ){
		return __URL.'/static/'.preg_replace('/^(\\\|\/)/', '', $path);
	}
	public function ago($date2, $date1=null){
		if(!$date1) $date1 = Date('Y-m-d H:i:s');
		$date_diff = date_diff(date_create($date2), date_create($date1));
		$str = "";

		if($date_diff->y > 0) $str = $date_diff->y." Years";
		else if($date_diff->m > 0) $str = $date_diff->m." Months";
		else if($date_diff->d > 0) $str = $date_diff->d." Days";
		else if($date_diff->h > 0) $str = $date_diff->h." Hours";
		else if($date_diff->i > 0) $str = $date_diff->i." Mins";
		else if($date_diff->s > 0) $str = $date_diff->s." Secs";

		return $str." ago";
	}
	public function route( $path ){
		return __URL.'/'.preg_replace('/^(\\\|\/)/', '', $path);
	}
	public function sessnoton($name, $url_or_callable) {
		if(!isset($_SESSION[$name])) {
			if( is_callable($url_or_callable) ){
				return $url_or_callable();
			} else {
				header('Location: '.$url_or_callable);
			}
		}
	}
	public function sesson($name, $url_or_callable) {
		if(isset($_SESSION[$name])) {
			if( is_callable($url_or_callable) ){
				return $url_or_callable();
			} else {
				header('Location: '.$url_or_callable);
			}
		}
	}
	public function uploads( $path ){
		return __URL.'/uploads/'.preg_replace('/^(\\\|\/)/', '', $path);
	}
	public function get( $name ){
		if( isset($_GET[$name]) ){
			return $_GET[$name];
		}
		return '';
	}
	public function post( $name ){
		if( isset($_POST[$name]) ){
			return $_POST[$name];
		}
		return '';
	}
	public function sess( $name ){
		if( isset($_SESSION[$name]) ){
			return $_SESSION[$name];
		}
		return '';
	}
	public function showWhen( $thing, $var_1, $var_2='' ){
		if( ($var_2 && $var_1==$var_2) || $var_1 ) {
			return $thing;
		}
		return '';
	}
}