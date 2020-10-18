<?php

	$lang = '';
	if( isset($_SESSION['language']) ){
		$lang = $_SESSION['language'];
	}

	L::load( $lang );

	function TableCreate( $config ){

		foreach ( $config['data_fields'] as $key => $value ) {
			if( is_numeric($key) ){
				$config['data_fields'][$key] = "!!this.$value!!";
			}
		}

		$_base = Template::parse('admin/table-layout', $config );

		$_base = preg_replace( '/(\(\(|\)\))/', '!!', $_base );

		return Template::parse_string( $_base, array(
			'data' => $config['data'],
		));

	}

	function deleteImage( $id ){
		$id = DB::table( 'collections' )
			->where( 'id', $id )
			->show();

		$id = end($id);
		if( $id ){
			@unlink( __BASE.'/uploads/.collections/'.$id['file'] );
			@unlink( __BASE.'/uploads/'.$id['file'] );
		}
	}
