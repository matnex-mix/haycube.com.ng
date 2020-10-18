<?php

class Orders {

	public function index( $_args ){

		$data = DB::table( '`order`', '*', function( &$row ){
			$user = DB::table( 'user' )
				->where( 'id', $row['user'] )
				->show();

			$row['user_name'] = end( $user )['name'];

			$pics = DB::table( 'collections', '*', function( &$row ){
					$loc = __BASE.'/uploads/'.$row['file'];
					if( !file_exists($loc) ){
						return FALSE;
					}
					$image = getimagesize($loc);
					$row['src'] = "data:${image['mime']};base64,".base64_encode(file_get_contents($loc));
			})
				->where( 'id', $row['collection'] )
				->show();

			$row['collection_obj'] = end( $pics );
		} )
			->order( 'id', 'DESC' )
			->where( '>downloads', '0' )
			->show();

		Template::__( 'admin/users', array(
			'table' => TableCreate(array(
				'table_action' => F::route( 'javascript:void(0)' ),
				'table_heading' => [
					'User',
					'Picture',
					'Downloads Remaining',
				],

				'table_options' => [
				],
				'row_options' => [
				],
				
				'data' => $data,
				'data_fields' => [
					'user' => '
						<a href="'.F::route( 'admin/!/users/#' ).'!!this.user!!">!!this.user_name!!</a>
					',
					'collection' => '
						<div class="d-flex">
							<div class="-b-image border mr-3" style="background-image: url(\'!!this.collection_obj.src!!\'); width: 60px; height: 60px; pointer-events: none;">		
							</div>

							<a href="'.F::route( 'admin/!/pictures/#' ).'!!this.collection!!">!!this.collection_obj.slug!!</a>
						</div>
					',
					'downloads',
				],

				'config' => [
					'check_option' => 1,
					'table_option' => 0,
				],
			)),
		) );

	}

}