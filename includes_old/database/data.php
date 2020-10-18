<?php

namespace SPhp\Database;

class Data {
	protected $function;
	protected $arguments;

	public function __construct( $f, ...$args ){
		$this->function = $f;
		$this->arguments = $args;
	}

	public function run(){
		return call_user_func_array($this->function, $this->arguments);
	}

	public function str($min, $max=100){
		return new Data(function($min, $max){
			$min_max = rand($min, $max);
			$lt_rand = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
			$rnd_text = "";
			for($x=0;$x<$min_max;$x++){
				$rnd_text .= $lt_rand[array_rand($lt_rand)];
			}
			return $rnd_text;
		}, $min, $max);
	}

	public function mixed($min, $max=100){
		return new Data(function($min, $max){
			$min_max = rand($min, $max);
			$lt_rand = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
			$rnd_text = "";
			for($x=0;$x<$min_max;$x++){
				$rnd_text .= $lt_rand[array_rand($lt_rand)];
			}
			return $rnd_text;
		}, $min, $max);
	}

	public function int($min, $max=100){
		return new Data(function($min, $max){
			return rand($min, $max);
		}, $min, $max);
	}
}