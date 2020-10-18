<?php

	if( !empty($_GET['k']) ){
		$k = $_GET['k'];

		$images = searchAndFilter($k);

		if( sizeof($images) ){
			Template::__('collection', array( 'collections'=>$images, 'error'=>0 ));
		} else {
			Template::__('collection', array( 'collections'=>[], 'error'=>1 ));
		}
	}