<?php

	global $options, $ex_filter, $page;

	Page::child('{page}', function($_ARGS){
		define('_PAGE', $_ARGS['page']);
	});

	if( !defined('_PAGE') ){
		define('_PAGE', 0);
	}

	$k = '';
	if( !empty($_GET['k']) ){
		$k = $_GET['k'];
	}

	$per_page = 28;
	$page = _PAGE;
	$images = searchAndFilter($k, $page);

	if( !empty($options['per_page']) ){
		$per_page = $options['per_page'];
	}

	function page( $url, $text, $class='' ){

		return "<a href='".F::route('')."!/$url' class='btn block-hover-success border rounded-0 ml-3 $class' style='min-width: 40px; height: 40px'>
			$text
		</a>";
	}

	$count = DB::query('SELECT count(`id`) i FROM `collections`')
		->fetch_assoc()['i'];
	$splits = ceil($count/$per_page);
	$pagination = '';

	if( $page>0 ){ $pagination .= page($page-1, '<i class="fa fa-chevron-left"></i>'); }
	if( $page>5 ){ $pagination .= page($page-5, $page-4); }
	if( $page>4 ){ $pagination .= page($page-4, $page-3); }
	if( $page>3 ){ $pagination .= page($page-3, $page-2).page('', '...', 'disabled'); }
	$pagination .= page('', $page+1, 'btn-success border-success disabled');
	if( $splits-$page>3 ){ $pagination .= page('', '...', 'disabled').page($page+3, $page+4); }
	if( $splits-$page>4 ){ $pagination .= page($page+4, $page+5); }
	if( $splits-$page>5 ){ $pagination .= page($page+5, $page+6); }
	if( $page<$splits-1 ){ $pagination .= page($page+1, '<i class="fa fa-chevron-right"></i>'); }

	Template::__('index', array( 'collections'=>$images, 'error'=>0, 'keyword'=>$k, 'ex_filters'=>extraFilters(), 'filters' => $options, 'filter_ex' => $ex_filter, 'page'=>$pagination ));