<?php

class Pictures {

	public function index( $_args ){

		$data = DB::table( 'collections', '*', function( &$row ){

			$author = DB::table( 'admins' )
				->where( 'id', $row['author'] )
				->show();

			$row['author_name'] = end( $author )['username'];

			$loc = __BASE.'/uploads/'.$row['file'];
			if( !file_exists($loc) ){
				return FALSE;
			}
			$image = getimagesize($loc);
			$row['src'] = "data:${image['mime']};base64,".base64_encode(file_get_contents($loc));

		} )
			->order( 'id', 'DESC' )
			->show();

		Template::__( 'admin/pictures', array(
			'table' => TableCreate(array(
				'table_action' => F::route( 'admin/admin/?a=picture' ),
				'table_heading' => [
					'File',
					'Price',
					'Discount',
					'Author',
				],

				'table_options' => [
					'delete',
				],
				'row_options' => [
					F::route('admin/!/pictures/delete/') => '<i class="fa fa-times-circle text-danger"></i> delete',
					F::route('admin/!/pictures/edit/') => '<i class="fa fa-pen text-warning"></i> edit',
				],

				'data' => $data,
				'data_fields' => [
					'file' => '
						<div class="d-flex">
							<div class="-b-image border mr-3" style="background-image: url(\'!!this.src!!\'); width: 60px; height: 60px; pointer-events: none;">
							</div>

							<a href="'.F::route( 'items/!/' ).'!!this.slug!!">!!this.title!!</a>
						</div>
					',
					'price' => '₦!!this.price!!',
					'discount' => '!!this.discount!!%',
					'author' => '
						!!var adm=F::sess(\'admin\')!!
						!!if this[\'author\']!=adm[\'id\']!!
						<a href="'.F::route('admin/!/admins/#').'!!this.author!!">
							!!this.author_name!!
						</a>
						!!endif!!
						!!if this[\'author\']==adm[\'id\']!!
						(me)
						!!endif!!
					',
				],

				'config' => [
					'check_option' => 1,
					'table_option' => 1,
				],
			)),
		) );

	}

	public function delete( $_args ){

		deleteImage( $_args['id'] );

		$qry = DB::delete( 'collections' )
			->where( 'id', $_args['id'] )
			->run();

		if( $qry==TRUE ){
			F::success( 'deleted!' );
			header('Location: '.F::route('admin/!/pictures'));
		} else {
			F::error( 'an unknown error ocurred' );
			header('Location: '.F::route('admin/!/pictures'));
		}

	}

	public function add( $_args ){

		Template::__( 'admin/add-picture', array(
			'action' => F::route( 'admin/admin?a=addPicture' ),
		) );

	}

	public function edit( $_args ){

		$dt = DB::table( 'collections' )
			->where( 'id', $_args['id'] )
			->show();
		$dt = end( $dt );

		if( !$dt ){
			header('Location: '.F::route('admin/!/pictures'));
		}

		foreach ($dt as $key => $value) {
			F::return_param( $key, $value );
		}

		Template::__( 'admin/add-picture', array(
			'action' => F::route( 'admin/admin?a=editPicture&i='.$_args['id'] ),
		) );

	}

}
