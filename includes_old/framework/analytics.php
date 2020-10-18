<?php

namespace SPhp\Framework;

use SPhp\Database\DB;

class Analytics {
	public function push(){
		$props = [];

		$props['value'] = "uri=>${_SERVER['REQUEST_URI']}|ip_address=>${_SERVER['REMOTE_ADDR']}|user_agent=>${_SERVER['HTTP_USER_AGENT']}";
		$props['time'] = intval($_SERVER["REQUEST_TIME"]);
		$props['option'] = "request";
		$props['`group`'] = session_id();
		$props['extra'] = "reload=>1";

		$check = DB::table('sphp_schema', ['id', 'extra'], array(
			"where" => [
				"`group`"=>session_id(),
				"+option"=>"request",
				"+value"=>$props["value"]
			]
		))->show();

		if( sizeof($check) ){
			DB::update('sphp_schema', array(
				"extra"=>"reload=>".(intval(explode('=>', $check[0]['extra'])[1])+1)."|status=>".http_response_code()
			), array(
				"id"=>$check[0]['id']
			));
		} else {
			DB::insert('sphp_schema', $props);
		}
	}
	public function pull(){

	}
}