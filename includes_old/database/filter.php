<?php

namespace SPhp\Database;

class Filter {
	private $data = [];
	
	function __construct( $data ) {
		$this->data = $data;
	}

	public function exkeys( $array_keys ) {
		foreach ($array_keys as $key) {
			unset($this->data[$key]);
		}

		return new Filter($this->data);
	}

	public function keys( $array_keys ) {
		$data = [];
		foreach ($array_keys as $key) {
			$data[$key] = $this->data[$key];
		}

		return new Filter($data);
	}

	public function where( $equals_array ) {
		if( empty($equals_array) ) {
			return new Filter($this->data);					
		}

		foreach ($this->data as $ikey=>$value) {

			$remove = true;
			foreach ($equals_array as $key=>$equals) {
				if( !isset($value[$key]) ) {
					die("Error: `$key` does not exist");
				}

				if( !is_array($equals) ) {
					$equals = [$equals];
				}

				if( is_array($value[$key]) ) {
					foreach ($equals as $equals_each) {
						if( in_array($equals_each, $value[$key]) ) {
							$remove = false;
							break 2;
						}
					}
				} else {

					if( in_array($value[$key], $equals) ) {
						$remove = false;
						break;
					}

					foreach ($equals as $e_value) {
						if( $e_value[0]=='*' ){
							if( preg_match('/'.substr($e_value, 1).'/', $value[$key]) ){
								$remove = false;
								break 2;
							}
						}
					}
				}
			}

			if($remove) {
				unset($this->data[$ikey]);
			}
		}

		return new Filter($this->data);
	}

	public function order( ...$order ) {
		if( isset($order) && is_array($order) && sizeof($this->data)>1 ) {

			if( !(isset($order[1]) && isset($order[0])) ) {
				die('Invalid order parameter');
			}
			$order_by = $order[0];
			$order = $order[1];

			if( is_string($order_by) ) {
				$order_by = [$order_by];
			} else if( !is_array($order_by) ) {
				die('Invalid order:by parameter');
			}

			foreach ($order_by as $by) {
				$temp = array_slice($this->data, 0, 1);
				if( !isset(end($temp)[$by]) ) {
					die("Invalid order:by parameter: column `$by` does not exist:");
				} else if( !is_string($by) ) {
					die("Invalid order:by parameter: column `$by` must be a typeof string");
				}

				for ($x=0;$x<sizeof($this->data);$x++) {
					for ($i=0;$i<sizeof($this->data)-1;$i++) {
						if($order=='DESC') {
							if( $this->data[$i+1][$by] > $this->data[$i][$by] ) {
								$temp = $this->data[$i+1];
								$this->data[$i+1] = $this->data[$i];
								$this->data[$i] = $temp;
							}
						} else if($order=='RAND') {
							shuffle($this->data);
							break(2);
						} else {
							if( $this->data[$i+1][$by] < $this->data[$i][$by] ) {
								$temp = $this->data[$i+1];
								$this->data[$i+1] = $this->data[$i];
								$this->data[$i] = $temp;
							}
						}
					}
				}
			}
		}

		return new Filter($this->data);
	}

	public function show( ...$limits ) {
		$filtered = [];

		if( isset($limits) && is_array($limits) && !empty($limits) ) {
			foreach ($limits as $limit) {
				if( is_array($limit) && sizeof($limit)>1 ) {
					$filtered = array_merge( $filtered, array_slice($this->data, $limit[0], $limit[1]) );
				} else if( is_int($limit) ) {
					$filtered = array_merge( $filtered, array_slice($this->data, $limit, 1) );
				} else {
					die('Invalid Limit parameter');
				}
			}
		} else {
			return $this->data;
		}

		return $filtered;
	}

	function atIndex( $array, $index ) {
		$temp = array_slice($array, $index, 1);
		return end($temp);
	}

	function insertAtIndex( $array, $index, $insertion ) {
		$head = array_slice($array, 0, $index);
		$tail = array_slice($array, $index+sizeof($insertion));
		$end = $head+$insertion+$tail;
		return $end;
	}
}