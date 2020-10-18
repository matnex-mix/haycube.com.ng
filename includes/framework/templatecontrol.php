<?php

namespace SPhp\Framework;

use Admin;

class TemplateControl {
	public function admin_action( $action, &$tmp ){
		return Admin::action($action);
	}

	public function data( $path, &$tmp, $def=FALSE ){
		$path = explode('.', $path);
		$current = $tmp->Template_Data;
		foreach ($path as $value) {
			$value_obj = explode('->', $value);
			$value = $value_obj[0];
			array_shift( $value_obj );

			if( isset($current[$value]) ) {
				$current = $current[$value];

				foreach ($value_obj as $value) {
					$current = $current->$value;
				}
			} else {
				if( $def ) return '';
				return '******';
			}
		}

		if( $def || gettype($current)=='object' ) return $current;
		return print_r($current, TRUE);
	}

	public function inc( $template, $data, &$tmp=null ){
		if( !is_array($data) ){
			$tmp = $data;
			$data = array();
		}
		return Template::parse( $template, array_merge( $tmp->Template_Data, $data ) );
	}

	public function run( $thing, &$T ){
		eval("return ($thing);");
	}

	public function use( $template, &$tmp ){
		$tmp->GLOBALS['extends_template'] = new Template( $template );
	}

	public function var( $key, $value, &$tmp ){

		$key = str_replace('.', '\'][\'', $key);
		$key = '$tmp->Template_Data[\''.$key.'\'] = $value;';

		eval($key);
	}

	public function yield( $block, &$tmp ){
		return TemplateControl::data( '$block_'.$block, $tmp, TRUE);
	}

	public function block( $name, &$tmp ){
		$tmp->GLOBALS['_parsing_block'] = '';
		$tmp->GLOBALS['block_name'] = $name;
		array_unshift($tmp->writeto, '_parsing_block');
	}

	public function endblock( &$tmp ){
		if( !isset($tmp->GLOBALS['_parsing_block']) ){
			Error::die([[
				1,
				'Founded an !!endblock()!! that is not preceeded by !!block()!!',
				'TEMPLATE_NOT_BLOCK'
			]]);
		}

		if( !isset($tmp->GLOBALS['block_content']) ){
			$tmp->GLOBALS['block_content'] = array();
		}

		$content = $tmp->GLOBALS['_parsing_block'];
		$tmp->GLOBALS['block_content']['$block_'.$tmp->GLOBALS['block_name']] = $content;

		array_shift($tmp->writeto);
		unset($tmp->GLOBALS['_parsing_block']);
	}

	public function for( $raw_data, &$tmp ){
		$data = TemplateControl::data( $raw_data, $tmp, $def=TRUE );
		if( !is_array($data) ){
			Error::die([[
				0,
				'Required (`Array`) for loop, (`'.gettype($raw_data).'`) given on for block !!for(\''.$raw_data.'\')!!',
				'TEMPLATE_FOR_INVALID_ARG'
			]]);
		}

		if( !isset($tmp->GLOBALS['_for_block']) ){
			$tmp->GLOBALS['_for_block'] = array();
			$tmp->GLOBALS['_for_data'] = array();
		}
		
		array_unshift($tmp->GLOBALS['_for_data'], $data);
		array_unshift($tmp->GLOBALS['_for_block'], '');
		array_unshift($tmp->writeto, '_for_block');
		$tmp->verbatim = true;
	}

	public function endfor( &$tmp ){
		if( empty($tmp->GLOBALS['_for_block']) ){
			Error::die([[
				1,
				'!!endfor()!! does not have a complementary !!for()!!',
				'TEMPLATE_NOT_FOR'
			]]);
		}

		preg_match('/!!for\(.*\)!!/', $tmp->GLOBALS['_for_block'][0], $fors);
		preg_match('/!!endfor\(\)!!/', $tmp->GLOBALS['_for_block'][0], $endfors);

		if( sizeof($endfors)<sizeof($fors) ){
			$tmp->verbatim = true;
			return '!!endfor()!!';
		}

		$gen = '';

		foreach ($tmp->GLOBALS['_for_data'][0] as $key => $value) {	
			$array = array_merge( $tmp->Template_Data, array( 'this' => $value, '&' => $key ) );
			$gen .= Template::parse_string( $tmp->GLOBALS['_for_block'][0], $array );
		}

		array_shift($tmp->writeto);
		array_shift($tmp->GLOBALS['_for_block']);

		return $gen;
	}

	public function if( $exp_array, &$tmp ){

		if( !isset($tmp->GLOBALS['_if_block']) ){
			$tmp->GLOBALS['_if_block'] = array();
			$tmp->GLOBALS['_if_meta'] = array();
		}

		array_unshift($tmp->GLOBALS['_if_block'], '');
		array_unshift($tmp->GLOBALS['_if_meta'], [ array( 'condition' => $exp_array, 'content' => '' ) ]);
		array_unshift($tmp->writeto, '_if_block');
	}

	public function elseif( $exp_array, &$tmp ){
		if( !isset($tmp->GLOBALS['_if_block']) ){
			Error::die([[
				1,
				'`elseif()` can be called after a complementary `if()` is called',
				'TEMPLATE_IF_SYNTAX'
			]]);
		}

		$tmp->GLOBALS['_if_meta'][0][0]['content'] = $tmp->GLOBALS['_if_block'][0];
		$tmp->GLOBALS['_if_block'][0] = '';
		array_unshift($tmp->GLOBALS['_if_meta'][0], array( 'condition' => $exp_array, 'content' => '' ));
	}

	public function else( &$tmp ){
		TemplateControl::elseif( true, $tmp );
	}

	public function endif( &$tmp ){
		if( !isset($tmp->GLOBALS['_if_block']) ){
			Error::die([[
				1,
				'`endif()` can be called after a complementary `if()` is called',
				'TEMPLATE_IF_SYNTAX'
			]]);
		}

		$tmp->GLOBALS['_if_meta'][0][0]['content'] = $tmp->GLOBALS['_if_block'][0];
		$nest = array_reverse($tmp->GLOBALS['_if_meta'][0]);

		array_shift($tmp->GLOBALS['_if_block']);
		array_shift($tmp->GLOBALS['_if_meta']);
		array_shift($tmp->writeto);

		foreach ($nest as $value) {
			if( TemplateControl::conditionBuilder( $value['condition'], $tmp ) ){
				return $value['content'];
			}
		}
	}

	public function conditionBuilder( $cond, &$tmp ){
		if( !is_array($cond) ){
			return $cond;
		}

		$prev_cond = true;
		foreach ($cond as $data => $value) {
			$and = preg_match('/^\+/', $data);
			if( $and ){
				$data = substr($data, 1);
			}

			$data = TemplateControl::data( $data, $tmp, TRUE );
			if( is_array($value) ){

				if( $and && !in_array($data, $value) ){
					return false;
				} elseif( in_array($data, $value) && !$and ){
					return true;
				} elseif( !$and ) {
					return false;
				}

			} else {
				if( $and && $data != $value ){
					return false;
				} elseif( $data == $value && !$and ){
					return true;
				} elseif( !$and ) {
					return false;
				}
			}
		}

		return true;
	}
}