<?php

class ExtraFilter {
	public function ex_gif( $image, $details ){
		if( $image['mime']!='image/gif' ){
			return false;
		}
		return true;
	}
	public function ex_background( $image, $details ){
		return true;
	}
}