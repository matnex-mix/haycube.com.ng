<?php

namespace SPhp\Admin;

use Page, Template;

class Admin {
	public function show(){
		$GLOBALS['paged'] = false;
		global $paged;
		$routes = array(
			'recovery' => function($_ARGS){
				global $paged;
				$paged = true;
				Admin::recovery($_ARGS);
			}
		);

		foreach ($routes as $key => $value) {
			Page::child($key, $value);
			if( $paged ){
				break;
			}
		}

		if(!$paged){
			Admin::index();
		}
	}

	/*
	 * Defined Route functions
	 *
	 */

	private function index(){
		echo Template::parse_string(
			Admin::header()
			.
			'
			'
			.
			Admin::footer()
		, array());
	}

	private function recovery( $args ){

	}

	private function header(){
		return '
<!DOCTYPE html>
<html>
	<head>
	</head>
	<body>
		';
	}

	private function footer(){
		return '
	</body>
</html>
		';
	}
}