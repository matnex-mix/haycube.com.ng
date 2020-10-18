<?php

namespace SPhp\Framework;

use SPhp\Database\DB;

class Analytics {
	public function create(){
		$check = DB::query("SHOW TABLES LIKE '%sphp_schema%';");
		if( $check->num_rows <= 0 ){
			DB::query("
				CREATE TABLE IF NOT EXISTS `sphp_schema` (
				  `id` int(10) UNSIGNED NOT NULL,
				  `group` varchar(100) NOT NULL,
				  `option` varchar(100) NOT NULL,
				  `value` varchar(300) NOT NULL,
				  `extra` varchar(500) NOT NULL,
				  `time` int(10) UNSIGNED NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
			", TRUE);
			DB::query("
				ALTER TABLE `sphp_schema` ADD PRIMARY KEY (`id`)
			", TRUE);
			DB::query("
				ALTER TABLE `sphp_schema` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17
			", TRUE);
		}
	}

	public function push(){
		Analytics::create();

		$props = [];

		$props['value'] = "uri=>${_SERVER['REQUEST_URI']}|ip_address=>${_SERVER['REMOTE_ADDR']}|user_agent=>${_SERVER['HTTP_USER_AGENT']}";
		$props['time'] = intval($_SERVER["REQUEST_TIME"]);
		$props['option'] = "request";
		$props['`group`'] = session_id();
		$props['extra'] = "reload=>1";

		$check = DB::table('sphp_schema', ['id', 'extra'])
			->where( "group", session_id() )
			->where( "+option", "request" )
			->where( "+value", $props["value"] )
			->show();

		if( sizeof($check) ){
			DB::update('sphp_schema', array(
				"extra"=>"reload=>".(intval(explode('=>', $check[0]['extra'])[1])+1)."|status=>".http_response_code()
			))
				->where( "id", $check[0]['id'] )
				->run();
		} else {
			DB::insert('sphp_schema', $props);
		}
	}
	public function pull(){

	}
}