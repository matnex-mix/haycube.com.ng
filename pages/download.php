<?php

	$GLOBALS['show'] = true;

	Page::child('{slug}', function($_ARGS){
		$item = DB::table('collections', 'slug, id, file')
			->where( 'slug', $_ARGS['slug'] )
			->show();
		$item = end($item);

		if( !$item ) return;

		$check = DB::table('`order`')
			->where( 'collection', $item['id'] )
			->where( '+user', $_SESSION['auth_user'] )
			->show();

		$check = end( $check );
		if( $check && $check['downloads']<=0 ) return;

		$loc = __BASE.'/uploads/.collections/'.$item['file'];
		if( file_exists( $loc ) ){
			header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($loc).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($loc));
            flush();
            readfile($loc);
            DB::update('`order`', array( 'downloads'=>$check['downloads']-1 ))
            	->where( 'id', $check['id'] )->run();
            die();
		}

		$GLOBALS['show'] = false;
	});

	if( $GLOBALS['show'] ) include '404.php';