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

	public function delete( $name, $where="", $show_error=FALSE ){
		global $__DBINSTANCE;
		$prepared_statement = [];

		if( isset($where) && is_array($where) ){
			if( !sizeof($where) ){
				$where = "";
			} else {
				$tmp_where = $where;
				$where = "WHERE";
				foreach ($tmp_where as $key => $value) {
					if( is_array($value) ){
						if( !sizeof($value) ){
							continue;
						}

						$tmp_val = $value;
						$value = "(";
						foreach ($tmp_val as $key2 => $value2) {
							if( $key2[0]=='+' ){
								$value .= " and ".substr($key2, 1)."=?";
								$prepared_statement[] = $value2;
							} else {
								$value .= " or $key2=?";
								$prepared_statement[] = $value2;
							}
						}
						$value .= " )";
						$value = preg_replace('/\( (and|or)/', '( ', $value);
						if( $key[0]=='+' ){
							$where .= " and $value";
						} else {
							$where .= " or $value";
						}
						continue;
					}

					if( $key[0]=='+' ){
						$where .= " and ".substr($key, 1)."=?";
						$prepared_statement[] = $value;
					} else {
						$where .= " or $key=?";
						$prepared_statement[] = $value;
					}
				}
				$where = preg_replace('/WHERE (and|or)/', 'WHERE', $where);
			}
		}

		$sql = DB::prepare("DELETE FROM `$name` $where", $prepared_statement);
		if( $__DBINSTANCE->query( $sql )===TRUE ){
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
		global $__DBINSTANCE;
		$prepared_statement = [];

		if( isset($columns) && is_array($columns) ){
			$columns = implode(',', $columns);
		}

		$sql = DB::prepare("SELECT $columns FROM `$name`", $prepared_statement);
		$result = $__DBINSTANCE->query( $sql );
		$result_array = [];
		if( $result && $result->num_rows > 0 ){
			while ( $row=$result->fetch_assoc() ) {
				if( $filter && is_callable($filter) ){
					if( $filter( $row )===FALSE ){
						continue;
					}
				}
				$result_array[] = $row;
			}
		} else {
			if( sizeof($__DBINSTANCE->error_list) ){
				$errors = [];
				print_r($__DBINSTANCE->error_list);
				foreach ($__DBINSTANCE->error_list as $value) {
					$errors[] = [
						1,
						$value['error'],
						'SQL_ERROR_'.$value['errno']
					];
				}
				Error::die($errors);
			}
		}

		return new Filter($result_array);
	}

	public function update( $name, $_opt, $where="", $show_error=FALSE ){
		global $__DBINSTANCE;
		$prepared_statement = [];

		if( !sizeof($_opt) ){
			return;
		}

		if( isset($where) && is_array($where) ){
			if( !sizeof($where) ){
				$where = "";
			} else {
				$tmp_where = $where;
				$where = "WHERE";
				foreach ($tmp_where as $key => $value) {
					if( is_array($value) ){
						if( !sizeof($value) ){
							continue;
						}

						$tmp_val = $value;
						$value = "(";
						foreach ($tmp_val as $key2 => $value2) {
							if( $key2[0]=='+' ){
								$value .= " and ".substr($key2, 1)."=?";
								$prepared_statement[] = $value2;
							} else {
								$value .= " or $key2=?";
								$prepared_statement[] = $value2;
							}
						}
						$value .= " )";
						$value = preg_replace('/\( (and|or)/', '( ', $value);
						if( $key[0]=='+' ){
							$where .= " and $value";
						} else {
							$where .= " or $value";
						}
						continue;
					}

					if( $key[0]=='+' ){
						$where .= " and ".substr($key, 1)."=?";
						$prepared_statement[] = $value;
					} else {
						$where .= " or $key=?";
						$prepared_statement[] = $value;
					}
				}
				$where = preg_replace('/WHERE (and|or)/', 'WHERE', $where);
			}
		}

		$key_values = [];
		$values_data = [];

		foreach ($_opt as $key => $value) {
			$key_values[] = "$key=?";
			$values_data[] = $value;
		}

		$key_values = implode(', ', $key_values);
		$values_data = array_merge( $values_data, $prepared_statement );

		$sql = DB::prepare("UPDATE `$name` SET $key_values $where", $values_data);
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

	public function prepare( $stmnt, $data ){
		global $__DBINSTANCE;
		foreach ($data as $value) {
			$value = str_replace('?', '@@qmark@@', $value);
			$value = mysqli_real_escape_string( $__DBINSTANCE, $value );
			if( is_numeric($value) ){
				$stmnt = preg_replace('/\?/', $value, $stmnt, 1);
			} else if( is_string($value) ){
				$stmnt = preg_replace('/\?/', '\''.$value.'\'', $stmnt, 1);
			} else if( is_array($value) ){

			} else if (is_bool($value)) {
				$stmnt = preg_replace('/\?/', $value, $stmnt, 1);
			}
		}
		$stmnt = str_replace('@@qmark@@', '?', $stmnt);
		return $stmnt;
	}

	public function query( $stmnt, $data=array() ){
		global $__DBINSTANCE;
		$sql = DB::prepare( $stmnt, $data );

		return $__DBINSTANCE->query( $sql );
	}

	public function seeder( $table_name, $data ){
		require_once __DIR__.'/seeder.php';
		return new Seeder($table_name, $data);
	}
}