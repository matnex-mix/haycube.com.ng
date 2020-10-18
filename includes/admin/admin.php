<?php

namespace SPhp\Admin;

use Page, Template, F, DB;

class Admin {
	public function setTable( $table ){
		$GLOBALS['AdminDataTable'] = $table;
	}
	public function getTable(){
		if( !isset($GLOBALS['AdminDataTable']) ){
			Admin::setTable( 'admins' );
		}

		return $GLOBALS['AdminDataTable'];
	}

	public function hook( $name, $callback ){
		$GLOBALS['AdminHook/'.$name] = $callback;
	}
	public function runHook( $name, &$arg, $context='' ){
		if( !empty( $GLOBALS['AdminHook/'.$name] && is_callable($GLOBALS['AdminHook/'.$name]) ) ){
			return $GLOBALS['AdminHook/'.$name]( $arg, $context );
		}

		return;
	}

	public function isCapable( $action ){}
	public function init(){

		$action = F::get('a');

		if( $action ){
			$action = Admin::$action();
		}

		if( $action ){
			header('Location: '.$action);
		}

	}
	public function action( $action, $id = '' ){
		return F::route( 'admin/?a='.($action).'&i='.$id );
	}
	
	public function table( $table_name, $transform ){

		return DB::table('books', '*', $transform)
			->show();

	}

	public function setCapability( $admin_id, $action ){}
	public function createAdmin( $username, $password ){}

	public function signedIn(){

		if( F::sess('admin_id') ){
			return TRUE;
		}

		return FALSE;

	}
	public function signIn( $user_name, $user_password ){

		$chk = DB::table( Admin::getTable() )
			->where( 'username', $user_name )
			->where( '+password', md5( 'SPHPADMIN-'.$user_password ) )
			->show();

		$chk = end($chk);

		if( $chk ){
			$_SESSION['admin_id'] = $chk['id'];
		} else {
			F::error('Invalid username or password');
			F::return_param( 'uname', $user_name );
		}

		header('Location: '.F::route(''));

	}
	public function signOut(){}


	/*
	 * Forms
	 */

	public function signInForm( $template_name, $callback=null ){

		if( Admin::signedIn() ){
			return;
		}

		$fields = array(
			'uname' => array(
				'name' => 'uname',
				'value' => F::get_param('uname'),
			),
			'upass' => array(
				'name' => 'upass',
				'value' => '',
			),
		);


		Template::__( $template_name, array(
			'fields' => $fields
		));

	}


	/*
	 * URL actions
	 */

	public function login() {

		$username = F::post('uname');
		$password = F::post('upass');

		if( $username && $password ){
		
			Admin::signIn( $username, $password );

		}

	}

	public function insert() {

		$rdr = F::post('dest_1');
		$rdr_error = F::post('dest_2');
		$i = F::get('i');

		if( $i ){

			unset($_POST['dest_1']);
			unset($_POST['dest_2']);
			unset($_POST['i']);

			foreach ($_POST as $key => $value) {
				F::return_param( $key, $value );
			}

			if( Admin::runHook( 'insert', $i, 'before_query' )=='break' ){
				return $rdr_error;
			}

			$query = DB::insert($i, $_POST);

			if( $query===TRUE ){
				F::success('Created Successfuly');
			} else {
				F::error('An error ocurred');
				$rdr = $rdr_error;
			}

			return $rdr;

		}

	}

}