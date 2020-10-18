<?php

namespace SPhp\Database;

use SPhp\Framework\Error;

class Filter {
	protected $target;
	protected $props;
	protected $process;

	protected $where = "";
	protected $order = "";
	protected $limit = "";
	protected $prepare_data = [];

	protected $rawColumns = false;
	protected $query = "SELECT ?c FROM ?t ?w ?o ?l";
	protected $model;
	
	protected function constructor(){}
	
	function __construct( $table, $columns, $filter=null ) {
		if( is_array($columns) && !$this->rawColumns ){
			$columns = implode(', ', $columns);
		}

		$this->target = $table;
		$this->props = $columns;
		$this->process = $filter;

		$this->constructor();
	}

	protected function build() {
		$stmnt = $this->query;
		$stmnt = str_replace('?c', $this->props, $stmnt);
		$stmnt = str_replace('?t', $this->target, $stmnt);
		$stmnt = str_replace('?w', $this->where, $stmnt);
		$stmnt = str_replace('?o', $this->order, $stmnt);
		$stmnt = str_replace('?l', $this->limit, $stmnt);

		$stmnt = DB::prepare($stmnt, $this->prepare_data);

		return $stmnt;
	}

	public function where( $prop, $compare, $wrapper='`' ) {

		$where_clause = "";
		$prop = explode('+', $prop);
		
		if( $this->where ){
			if( $prop[0]=='' ) $where_clause .= " AND";
			else $where_clause .= " OR";
		} else {
			$where_clause .= "WHERE";
		}

		$opt = '=';
		$prop = end($prop);

		if( strpos($prop, '!')===0 ){

			$prop = substr($prop, 1);
			$opt = '!=';

		} else if( strpos($prop, '>')===0 ){

			$prop = substr($prop, 1);
			$opt = '>';

		}

		if( strpos($compare, '*')===0 ){
			$compare = substr( $compare, 1 );
			$opt = 'LIKE';
		}

		$where_clause .= " $wrapper".$prop."$wrapper $opt ?";
		$this->prepare_data[] = $compare;
		$this->where .= $where_clause;

		return $this;
	}

	public function order( $prop, $by='ASC' ) {
		$order_clause = "";
		
		if( $this->order ){
			$order_clause .= " ,";
		} else {
			$order_clause .= "ORDER BY";
		}

		if( $prop ){
			$prop = "`$prop`";
		}

		$order_clause .= " $prop $by";
		$this->order .= $order_clause;

		return $this;
	}

	public function model( $class ){
		if( !class_exists( $class ) ){
			Error::die([[
				1,
				'Model class ('.$class.') not found',
				'DB_INVALID_MODEL'
			]]);
		}

		$this->model = $class;
		return $this;
	}

	public function parse( $result ){
		if( isset( $result->num_rows ) && $result->num_rows >= 0 ){
			$result_array = [];
			
			while ( $row=$result->fetch_assoc() ) {
				if( $this->process && is_callable($this->process) ){
					$process = $this->process;
					if( $process( $row )===FALSE ){
						continue;
					}
				}

				if( $this->model ){
					$row = new $this->model( $row );
				}

				$result_array[] = $row;
			}

			return $result_array;
		} else {
			return $result;
		}
	}

	public function show( $offset=0, $count=100, $error=TRUE ) {
		$this->limit = "LIMIT $offset, $count";

		global $__DBINSTANCE;
		$res = $__DBINSTANCE->query( $this->build() );
		
		if( $error && sizeof($__DBINSTANCE->error_list) ){
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

		return $this->parse( $res );
	}
}

class updateFilter extends Filter {

	protected $query = "UPDATE `?t` SET ?c ?w";
	protected $rawColumns = true;
	public $error = false;

	protected function constructor(){
		$tmp = '';

		foreach ($this->props as $key => $value) {
			$tmp .= ", `$key` = ?";
			$this->prepare_data[] = $value;
		}

		$tmp = substr($tmp, 2);
		$this->props = $tmp;
	}

	public function run(){
		return $this->show( 0, 1, $this->error );
	}

}

class DeleteFilter extends Filter {

	protected $query = "DELETE FROM `?t` ?w";
	public $error = false;

	public function run(){
		return $this->show( 0, 1, $this->error );
	}

}