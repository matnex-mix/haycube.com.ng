<?php

namespace SPhp\Database;

use SPhp\Framework\Error;

use mysqli;

class DB {
	public function connect(){
		if( empty($GLOBALS['__DBINSTANCE']) ){
			$GLOBALS['__DBINSTANCE'] = new mysqli( __DBHOST, __DBUSER, __DBPASS, __DBNAME );
			if( mysqli_connect_error() ){
				Error::die([[
					1,
					'Error establishing database connection',
					'DATABASE_FAILURE'
				]]);
			}
		}
	}

	public function close(){
		if( !empty($GLOBALS['__DBINSTANCE']) ){
			$GLOBALS['__DBINSTANCE']->close();
		}
	}

	public function delete( $name, $show_error=FALSE ){
		$_tmp = new DeleteFilter( $name, '' );
		$_tmp->error = $show_error;

		return $_tmp;
	}

	public function insert( $name, $_opt, $show_error=FALSE ){
		global $__DBINSTANCE;

		if( !sizeof($_opt) ){
			return;
		}

		$keys = array_keys($_opt);
		foreach ($keys as $index => $value) {
			if( $value[0]!='`' && $value[strlen($value)-1]!='`' ){
				$keys[$index] = "`$value`";
			}
		}
		$keys = implode(', ', $keys);

		$values_data = array_values($_opt);
		$values = str_repeat(", ?", sizeof($values_data));
		$values = substr($values, 2);

		$sql = DB::prepare("INSERT INTO `$name` ($keys) VALUES($values)", $values_data);
		if( $__DBINSTANCE->query( $sql ) === TRUE ){
			return true;
		} else if( $show_error ) {
			Error::die([[
				0,
				'Insertion failed: '.$__DBINSTANCE->error,
				'SQL_INSERT_ERROR'
			]]);
		}

		return false;
	}

	public function table( $name, $columns='*', $filter=null ){
		return new Filter($name, $columns, $filter);
	}

	public function update( $name, $_opt, $show_error=FALSE ){
		$_tmp = new UpdateFilter( $name, $_opt );
		$_tmp->error = $show_error;

		return $_tmp;
	}

	public function prepare( $stmnt, $data ){
		global $__DBINSTANCE;
		foreach ($data as $value) {
			$value = str_replace('?', '@@qmark@@', $value);
			$value = mysqli_real_escape_string( $__DBINSTANCE, $value );
			if( is_numeric($value) ){
				$stmnt = preg_replace('/\?/', $value, $stmnt, 1);
			} else if( strpos($value, '`')===0 ) {
				$stmnt = preg_replace('/\?/', substr($value, 1), $stmnt, 1);
			} else if( is_string($value) ){
				$stmnt = preg_replace('/\?/', '\''.$value.'\'', $stmnt, 1);
			} else if( is_array($value) ){

			} else if( is_bool($value) ) {
				$stmnt = preg_replace('/\?/', $value, $stmnt, 1);
			}
		}
		$stmnt = str_replace('@@qmark@@', '?', $stmnt);
		#die($stmnt);
		return $stmnt;
	}

	public function query( $stmnt, $show_error=FALSE, $data=array() ){
		global $__DBINSTANCE;
		$query = $__DBINSTANCE->query( DB::prepare( $stmnt, $data ) );

		if( $show_error && sizeof($__DBINSTANCE->error_list) ){
			$errors = [];
			foreach ($__DBINSTANCE->error_list as $value) {
				$errors[] = [
					1,
					$value['error'],
					'SQL_ERROR_'.$value['errno']
				];
			}
			Error::die($errors);
		}

		return $query;
	}

	public function seeder( $table_name, $data ){
		require_once __DIR__.'/seeder.php';
		return new Seeder($table_name, $data);
	}
}