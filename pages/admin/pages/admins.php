<?php

class Admins {

	public function add( $_args ){

		Template::__( 'admin/add-admin', array(
			'action' => F::route('admin/admin?a=addAdmin'),
		) );

	}

	public function index( $_args ){

		$data = DB::table( 'admins' )
			->where( '!id', $_SESSION['admin']['id'] )
			->order( 'last_login', 'DESC' )
			->show();

		Template::__( 'admin/admins', array(
			'table' => TableCreate(array(
				'table_action' => F::route( 'admin/admin/?a=admin' ),
				'table_heading' => [
					'Username',
					'Capability',
					'Last Login',
					'Status',
				],

				'table_options' => [
					'delete', 'suspend', 'unsuspend'
				],
				'row_options' => [
					F::route('admin/!/admins/delete/') => '<i class="fa fa-times-circle text-danger"></i> delete',
					F::route('admin/!/admins/edit/') => '<i class="fa fa-pen text-warning"></i> edit',
				],

				'data' => $data,
				'data_fields' => [
					'username',
					'capability',
					'last_login',
					'status',
				],

				'config' => [
					'check_option' => 1,
					'table_option' => 1,
				],
			)),
		));

	}

	public function edit( $_args ){

		$data = DB::table( 'admins' )
			->where( 'id', $_args['id'] )
			->show();
		$data = end( $data );

		if( $data ){
			F::return_param( 'u_name', $data['username'] );
			F::return_param( 'u_role', $data['capability'] );
		} else {
			header('Location: '.F::route( 'admin/!/admins' ));
		}

		Template::__( 'admin/add-admin', array(
			'action' => F::route('admin/admin?a=editAdmin&i='.$_args['id'] ),
		) );

	}

	public function delete( $_args ){

		$qry = DB::delete( 'admins' )
			->where( 'id', $_args['id'] )
			->run();

		if( $qry===TRUE ){
			F::success( 'deleted!' );
		} else {
			F::error( 'an error ocurred!' );
		}

		header('Location: '.F::route( 'admin/!/admins' ));

	}

}
