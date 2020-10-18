<?php

namespace SPhp\Framework;

class Framework {
	public function boot( $base ){
		if( defined('__BASE') ){
			die( 'Booted Already' ); # ERROR: 1
		}
		define('__BASE', dirname( $base ));

		if( !file_exists(__BASE.'/config.json') ){
			die( 'Cannot Find Configuration File' ); # ERROR: 2
		}
		$config = json_decode( file_get_contents(__BASE.'/config.json') );

		if( JSON_ERROR_NONE !== json_last_error() ){
			die( 'Configuration File not formatted properly' ); # ERROR: 3
		} else {
			define( '__URL', $config->URL );
			define( '__HOME', $config->HOME );
			define( '__DBNAME', $config->DBNAME );
			define( '__DBHOST', $config->DBHOST );
			define( '__DBUSER', $config->DBUSER );
			define( '__DBPASS', $config->DBPASS );
			define( '__LANG', $config->LANGUAGE );
			define( '__RECOVERY', json_decode(json_encode($config->RECOVERY), true) );

			if( $config->LANGUAGE ){
				Lang::load( $config->LANGUAGE );
			}
		}

		define( '__CONFIGURED', true );
	}
	public function manage( $path ){
		global $Response;
		$target_file = __BASE.'/pages/404.php';

		if( defined('__CONFIGURED') ){
			$path = str_replace(__HOME, '', $path);
			//preg_match('/(?:(?:\/)!((?:\/[^\/]*)+)\??.*)$/', $path, $not_path);
			$path = preg_split('/\?/', $path, 2)[0];
			$not_path = preg_split('/!/', $path, 2);
			if( isset($not_path[0]) ){
				$path = $not_path[0];
			}
			if( isset($not_path[1]) ){
				$not_path = explode('/', $not_path[1]);
				foreach ($not_path as $value) {
					if( $value ) $Response->url_data( $value );
				}
			}
			
			define( '__SEEK', $path );

			if( $path=='/' ){
				$target_file = __BASE.'/pages/index.php';
			} else{
				$level_path = explode('/', $path);
				$path = __BASE.'/pages'.$path;
				if( preg_match('/.php$/', end($level_path)) && file_exists($path) ){
					$target_file = $path;
				} else if( end($level_path)=='' && file_exists($path.'index.php') ){
					$target_file = $path.'index.php';
				} else if( end($level_path)=='' && file_exists(substr($path, 0, strlen($path)-1).'.php') ){
					$target_file = substr($path, 0, strlen($path)-1).'.php';
				} else if( file_exists($path.'.php') ){
					$target_file = $path.'.php';
				} else if( file_exists($path.'/index.php') ){
					$target_file = $path.'/index.php';
				} else if( !file_exists($target_file) ){
					http_response_code(404);
					$Response->__('
<!DOCTYPE html>
<html>
<head>
</head>
<body>
	<h1>404: Not Found</h1>
	<p>
		The requested url was not found on this server. Please check your spelling and try again<br/>
		<br/><i>'.__URL.__SEEK.'</i>
	</p>
	<hr/>
	<strong>Strongly Powered by SPhp&reg;</strong>
</body>
</html>
					');
					return;
				}
			}

			require_once $target_file;
		}
	}
	public function secure(){
		foreach ($_GET as $key => $value) {
			$_GET[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
		}
		foreach ($_POST as $key => $value) {
			$_POST[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
		}
	}
}