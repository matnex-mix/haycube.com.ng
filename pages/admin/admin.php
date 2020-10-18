<?php

$a = F::get('a');
$o = F::post('o');

if( $a=='login' ){

	$user = F::post('hay_uname');
	$pass = F::post('hay_pass');

	$try_admin = DB::table('admins')
		->where( 'username', $user )
		->where( 'password', md5( $pass ) )
		->show();
	$try_admin = end( $try_admin );

	if( $try_admin ){
		$_SESSION['admin'] = $try_admin;

		header('Location: '.F::route('admin'));
		die('');
	}

	header('Location: '.F::route('admin/login?error='.urlencode('invalid username or password')));

} elseif ( $a=='addAdmin' ) {

	$user = F::post('u_name');
	$pass = F::post('u_pass');
	$role = F::post('u_role');

	$try = DB::table( 'admins' )
		->where( 'username', $user )
		->show();

	if( sizeof( $try ) ){
		F::error( 'username exists already' );
		header('Location: '.F::route( "admin/!/admins/add" ));
	} else {

		$put = DB::insert( 'admins', array(
			'username' => $user,
			'password' => md5( $pass ),
			'capability' => $role,
			'status' => 1,
		));

		F::return_param( 'u_name', $user );
		F::return_param( 'u_role', $role );

		if( $put === TRUE ){
			F::success( 'admin created!' );
			header('Location: '.F::route( "admin/!/admins/" ));
		} else {
			F::error( 'an unknown error ocurred' );
			header('Location: '.F::route( "admin/!/admins/add" ));
		}

	}

} elseif ( $a=='editAdmin' ) {

	$id = F::get('i');

	if( !$id ){
		F::error( 'an error ocurred!' );
		header('Location: '.F::route( "admin/!/admins" ));
	}

	$user = F::post('u_name');
	$pass = F::post('u_pass');
	$role = F::post('u_role');

	$data_1 = array();

	if( $user ){
		$data_1['username'] = $user;
	}

	if( $pass ){
		$data_1['password'] = md5( $pass );
	}

	if( $role ){
		$data_1['capability'] = $role;
	}

	$put = DB::update( 'admins', $data_1 )
		->where( 'id', $id )
		->run();

	F::return_param( 'u_name', $user );
	F::return_param( 'u_role', $role );

	if( $put === TRUE ){
		F::success( 'changes saved!' );
		header('Location: '.F::route( "admin/!/admins" ));
	} else {
		F::error( 'an unknown error ocurred' );
		header('Location: '.F::route( "admin/!/admins/edit/".$id ));
	}

} elseif ( $a == 'admin' && $o == 'suspend' ) {

	$items = F::group_post_param( 'item' );
	$qry = DB::update( 'admins', array(
		'status' => 0,
	) );

	foreach ($items as $value) {
		$qry = $qry->where( 'id', $value );
	}

	if( $qry->run()===TRUE ){
		F::success( 'admins suspended!' );
	} else {
		F::error( 'an error ocurred' );
	}

	header('Location: '.F::route('admin/!/admins/'));

} elseif ( $a == 'admin' && $o == 'unsuspend' ) {

	$items = F::group_post_param( 'item' );
	$qry = DB::update( 'admins', array(
		'status' => 1,
	) );

	foreach ($items as $value) {
		$qry = $qry->where( 'id', $value );
	}

	if( $qry->run()===TRUE ){
		F::success( 'admins unsuspended!' );
	} else {
		F::error( 'an error ocurred' );
	}

	header('Location: '.F::route('admin/!/admins/'));

} elseif ( $a == 'admin' && $o == 'delete' ) {

	$items = F::group_post_param( 'item' );
	$qry = DB::delete( 'admins' );

	foreach ($items as $value) {
		$qry = $qry->where( 'id', $value );
	}

	if( $qry->run()===TRUE ){
		F::success( 'admins deleted!' );
	} else {
		F::error( 'an error ocurred' );
	}

	header('Location: '.F::route('admin/!/admins/'));

} elseif ( $a == 'user' && $o == 'suspend' ) {

	$items = F::group_post_param( 'item' );
	$qry = DB::update( 'user', array(
		'status' => 0,
	) );

	foreach ($items as $value) {
		$qry = $qry->where( 'id', $value );
	}

	if( $qry->run()===TRUE ){
		F::success( 'user(s) suspended!' );
	} else {
		F::error( 'an error ocurred' );
	}

	header('Location: '.F::route('admin/!/users/'));

} elseif ( $a == 'user' && $o == 'unsuspend' ) {

	$items = F::group_post_param( 'item' );
	$qry = DB::update( 'user', array(
		'status' => 1,
	) );

	foreach ($items as $value) {
		$qry = $qry->where( 'id', $value );
	}

	if( $qry->run()===TRUE ){
		F::success( 'user(s) unsuspended!' );
	} else {
		F::error( 'an error ocurred' );
	}

	header('Location: '.F::route('admin/!/users/'));

} elseif ( $a == 'addCurr' ) {

	$abbr = F::post( 'abbr' );
	$symbol = F::post( 'symbol' );
	$rate = F::post( 'rate' );

	$qry = DB::insert( 'currency', array(
		'abbr' => $abbr,
		'symbol' => $symbol,
		'rate' => $rate,
	) );

	F::return_param( 'abbr', $abbr );
	F::return_param( 'symbol', $symbol );
	F::return_param( 'rate', $rate );

	if( $qry===TRUE ){
		F::success( 'added!' );
		header( 'Location: '.F::route('admin/!/currency/') );
	} else {
		F::error( 'an error ocurred!' );
		header( 'Location: '.F::route('admin/!/currency/add') );
	}

} elseif ( $a == 'editCurr' ) {

	$i = F::get( 'i' );

	$abbr = F::post( 'abbr' );
	$symbol = F::post( 'symbol' );
	$rate = F::post( 'rate' );

	$data_1 = array();
	if( $abbr )	$data_1['abbr'] = $abbr;
	if( $symbol ) $data_1['symbol'] = $symbol;
	if( $rate ) $data_1['rate'] = $rate;

	$qry = DB::update( 'currency', $data_1 )
		->where( 'id', $i )
		->run();

	F::return_param( 'abbr', $abbr );
	F::return_param( 'symbol', $symbol );
	F::return_param( 'rate', $rate );

	if( $qry===TRUE ){
		F::success( 'changes were successfull!' );
		header( 'Location: '.F::route('admin/!/currency/') );
	} else {
		F::error( 'an error ocurred!' );
		header( 'Location: '.F::route('admin/!/currency/edit/'.$i) );
	}

} elseif ( $a == 'currency' && $o == 'delete' ) {

	$items = F::group_post_param( 'item' );
	$qry = DB::delete( 'currency' );

	foreach ($items as $value) {
		$qry = $qry->where( 'id', $value );
	}

	if( $qry->run()===TRUE ){
		F::success( 'currenc(y)(ies) deleted!' );
	} else {
		F::error( 'an error ocurred' );
	}

	header('Location: '.F::route('admin/!/currency'));

} elseif ( $a == 'picture' && $o == 'delete' ) {

	$items = F::group_post_param( 'item' );
	$qry = DB::delete( 'collections' );

	foreach ($items as $value) {
		$qry = $qry->where( 'id', $value );
		deleteImage( $value );
	}

	if( $qry->run()===TRUE ){
		F::success( 'picture(s) deleted!' );
	} else {
		F::error( 'an error ocurred' );
	}

	header('Location: '.F::route('admin/!/pictures'));

} elseif ( $a=='addPicture' ) {

	$check_file = File::upload(array(
		'file' => array(
			'name' => md5( time() ),
			'ext' => File::image(),
			'path' => __BASE.'/uploads/.collections/'
		),
	));

	if( $check_file['file'][0]===TRUE ){

		$file_name = str_replace( '.collections/', '', $check_file['file'][1] );

		copy( __BASE.$check_file['file'][1], __BASE.$file_name );
		processImage( __BASE.$file_name );
		//compress image and put watermark
		$_POST['file'] = str_replace( '/uploads/', '', $file_name);

		$_POST['date'] = Date('Y-m-d H:i:s');
		$_POST['slug'] = strtolower( str_replace( ' ', '-', preg_replace( '/[^\w]/', '-', $_POST['title'] ) ));
		$_POST['author'] = $_SESSION['admin']['id'];


		$image = getimagesize( __BASE.$file_name );
		$o_img = imagecreatefromstring(file_get_contents(__BASE.$file_name));
		imagecopyresampled ( $o_img , $o_img , 0, 0, 0, 0, 1, 1, $image[0], $image[1] );
		$rgb = imagecolorat($o_img, 0, 0);
		$r = ($rgb >> 16) & 0xFF;
		$g = ($rgb >> 8) & 0xFF;
		$b = $rgb & 0xFF;
		$_POST['color'] = "rgba($r,$g,$b)";

		$qry = DB::insert( 'collections', $_POST );

		if( $qry===TRUE ){
			F::success( 'collection added!' );
			header('Location: '.F::route( 'admin/!/pictures' ));
			die('');
		} else {
			F::error( 'an error ocurred' );
		}

	} else {
		F::error( $check_file['file'][1] );
	}

	header('Location: '.F::route( 'admin/!/pictures/add' ));

} elseif ( $a=='editPicture' ) {

	$i = F::get('i');

	if( !empty( $_FILES['file']['tmp_name'] ) ){

		$check_file = File::upload(array(
			'file' => array(
				'name' => md5( time() ),
				'ext' => File::image(),
				'path' => __BASE.'/uploads/.collections/'
			),
		));

		if( $check_file['file'][0]===TRUE ){

			$file_name = str_replace( '.collections/', '', $check_file['file'][1] );

			copy( __BASE.$check_file['file'][1], __BASE.$file_name );
			processImage( __BASE.$file_name );
			//compress image and put watermark
			$_POST['file'] = str_replace( '/uploads/', '', $file_name);

			$image = getimagesize( __BASE.$file_name );
			$o_img = imagecreatefromstring(file_get_contents(__BASE.$file_name));
			imagecopyresampled ( $o_img , $o_img , 0, 0, 0, 0, 1, 1, $image[0], $image[1] );
			$rgb = imagecolorat($o_img, 0, 0);
			$r = ($rgb >> 16) & 0xFF;
			$g = ($rgb >> 8) & 0xFF;
			$b = $rgb & 0xFF;
			$_POST['color'] = "rgba($r,$g,$b)";

		} else {
			F::error( $check_file['file'][1] );
			header('Location: '.F::route('admin/!/pictures/edit/'.$i));
			die('');
		}

	}

	foreach ($_POST as $key => $value) {
		if( !$value ){
			unset( $_POST[$key] );
		}
	}

	$qry = DB::update( 'collections', $_POST )
		->where( 'id', $i )
		->run();

	if( $qry===TRUE ){
		F::success( 'changes saved!' );
		header('Location: '.F::route( 'admin/!/pictures' ));
		die('');
	} else {
		F::error( 'an error ocurred' );
		header('Location: '.F::route( 'admin/!/pictures/edit/'.$i ));
	}

} else {
	include __DIR__.'/../404.php';
}

function processImage( $file ){

	$watermarkImg = imagecreatefrompng(__BASE.'/static/img/watermark.png');
	$im = imagecreatefromstring(file_get_contents($file));

    $sx = imagesx($watermarkImg);
    $sy = imagesy($watermarkImg);

		$rp = (imagesx($im) - $sx)/2;
		$bp = (imagesy($im) - $sy)/2;

    imagecopy($im, $watermarkImg, $rp, $bp, 0, 0, $sx, $sy);
		imagecopy($im, $watermarkImg, $rp, 10, 0, 0, $sx, $sy);
		imagecopy($im, $watermarkImg, $rp, imagesy($im) - $sy - 10, 0, 0, $sx, $sy);

    imagejpeg($im, $file, 30);
    imagedestroy($im);

}
