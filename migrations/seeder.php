<?php

namespace SPhp\Migration;

require_once __DIR__.'/../includes/autoload.php';

use SPhp\Framework\Framework;
use SPhp\File\File;
use SPhp\Database\DB;
use SPhp\Database\Data;

Framework::boot( __DIR__.'../' );
DB::connect();

/*$main = DB::seeder('collections', array(
	'slug'=>'',
	'title'=>'',
	'file'=>'',
	'tags'=>'',
	'author'=>1
));

$files = File::dir(__BASE.'/uploads', '/.*\.jpg/', File::image());
foreach ($files as $key => $value) {
	$slug = preg_replace('/\.\w+$/', '', $value);
	$title = preg_replace('/(-|_)/', ' ', $slug);
	$tags = preg_replace('/ /', ',', $title);
	$main->swap([
		'slug'=>$slug,
		'title'=>$title,
		'file'=>$value,
		'tags'=>$tags,
		'date'=>Date('Y-m-d H:i:s')
	])->repeat(1);
}*/

/*DB::table('collections', '*', function( &$row ){
	$loc = __BASE.'/uploads/'.$row['file'];
	if( !file_exists($loc) ){
		return FALSE;
	}
	$image = getimagesize($loc);
	$o_img = imagecreatefromstring(file_get_contents($loc));
	imagecopyresampled ( $o_img , $o_img , 0, 0, 0, 0, 1, 1, $image[0], $image[1] );
	$rgb = imagecolorat($o_img, 0, 0);
	$r = ($rgb >> 16) & 0xFF;
	$g = ($rgb >> 8) & 0xFF;
	$b = $rgb & 0xFF;
	// $a = 1 - ((($rgb & 0x7F000000) >> 24)/127);
	$color = "rgba($r,$g,$b)";
	DB::update('collections', array(
		'color'=>$color
	), array(
		'where'=>[ 'id'=>$row['id'] ]
	));
});*/

/*DB::seeder('user', array(
	'name' => 'matnex',
	'email' => 'matnex@gmail.com',
	'password' => md5('matnex'),
	'token' => Data::str(50, 70),
	'created_at' => Date('Y-m-d H:i:s')
))
	->repeat(1)
	->swap(array(
		'name' => 'matnex-mix',
		'email' => 'matnex.mix@gmail.com',
		'password' => md5('matnex-mix'),
		'token' => Data::str(50, 70),
		'created_at' => Date('Y-m-d H:i:s')
	))
	->repeat(1);*/

DB::seeder('admins', array(
	'username' => 'matnex',
	'password' => 'matnexlee',
	'last_login' => Date('Y-m-d H:i:s'),
	'capability' => 'SuperAdmin',
))
	->repeat(1)
;

DB::close();