<?php

class CurrencyPage {

	public function index( $_args ){

		$data = DB::table( 'currency' )
			->show();

		Template::__( 'admin/currency', array(
			'table' => TableCreate(array(
				'table_action' => F::route( 'admin/admin/?a=currency' ),
				'table_heading' => [
					'Abbreviation',
					'Symbol',
					'Rate',
					'Last Update',
				],

				'table_options' => [
					'delete'
				],
				'row_options' => [
					F::route('admin/!/currency/edit/') => '<i class="fa fa-pen text-warning"></i> edit',
				],
				
				'data' => $data,
				'data_fields' => [
					'abbr',
					'symbol',
					'rate',
					'last_update',
				],

				'config' => [
					'check_option' => 1,
					'table_option' => 1,
				],
			)),
		) );

	}

	public function add( $_args ){

		Template::__( 'admin/add-currency', array(
			'action' => F::route( 'admin/admin?a=addCurr' ),
		) );

	}

	public function edit( $_args ){

		$data = DB::table( 'currency' )
			->where( 'id', $_args['id'] )
			->show();
		$data = end($data);

		if( !$data ){
			header('Location: '.F::route('admin/!/currency'));
			die('');
		}

		foreach ($data as $key => $value) {
			F::return_param( $key, $value );
		}

		Template::__( 'admin/add-currency', array(
			'action' => F::route( 'admin/admin?a=editCurr&i='.$_args['id'] ),
		) );

	}

}