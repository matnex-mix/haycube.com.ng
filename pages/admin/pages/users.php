<?php

class Users {
	public function index( $_args ){

		$data = DB::table( 'user' )
			->order( 'created_at', 'DESC' )
			->show();

		Template::__( 'admin/users', array(
			'table' => TableCreate(array(
				'table_action' => F::route( 'admin/admin/?a=user' ),
				'table_heading' => [
					'Name',
					'Email',
					'Registeration Date',
					'Status',
				],

				'table_options' => [
					'suspend', 'unsuspend'
				],
				'row_options' => [
				],
				
				'data' => $data,
				'data_fields' => [
					'name',
					'email',
					'created_at',
					'status',
				],

				'config' => [
					'check_option' => 1,
					'table_option' => 0,
				],
			)),
		) );

	}
}