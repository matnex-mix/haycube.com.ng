<?php

namespace SPhp\Migration;

require_once '../includes/autoload.php';
use SPhp\Framework\Framework;
use mysqli;

class Migrate {
	protected $db;
	protected $queries = [];

	public function __construct( $file ){
		$migration = '{'.file_get_contents(__DIR__.'/'.$file).'}';
		$migration = json_decode( $migration );
		if( JSON_ERROR_NONE !== json_last_error() ){
			die( 'Migration file not well formatted' );
		}

		$this->db = new mysqli( __DBHOST, __DBUSER, __DBPASS, __DBNAME ) or die( 'Datbase Connection denied' );

		foreach ($migration as $key => $value) {
			if( in_array( $key, get_class_methods($this) ) ){
				$this->$key( $value );
			} else {
				die( 'Invalid migration command ('.$key.')' );
			}
		}

		$this->run();
		copy(__DIR__.'/'.$file, __DIR__.'/SPhp-Migration-'.Date('Ymd_His').'.json');
		$this->showQueriesState();
	}

	public function create( $Tables ){
		foreach ($Tables as $Table => $Columns) {
			$sql = "CREATE TABLE `$Table`( ";
			$primary_key = null;
			foreach ($Columns as $name => $def) {
				$type = ''; $length = 0; $default = 0; $null = false; $after = ''; $ai = 0;

				if( empty($def->type) ){
					die( 'Type not defined' );
				}

				$type = $def->type;
				if( !empty($def->length) ) $length = $def->length;
				if( !empty($def->null) ) $null = $def->null;
				if( !empty($def->default) ) $default = $def->default;
				if( !empty($def->after) && $name != $def->after ) $after = $def->after;
				if( !empty($def->auto_increment) ) $ai = $def->auto_increment;

				$sql .= ("`$name` ".$type.( $length ? '('.$length.')' : ' unsigned' )." " . ( $null ? 'NULL' : 'NOT NULL') . ( $ai ? ' AUTO_INCREMENT' : '' ) . " " . ( $default ? 'DEFAULT '.$default : '' ) . ( $after ? ' AFTER `'.$after.'`' : '' ) . ", " );
				if( !empty($def->primary) ){
					$primary_key = "PRIMARY KEY(`$name`)";
				}
			}
			$sql = substr( $sql, 0, strlen($sql)-2 );
			$sql .= ( $primary_key ? ', '.$primary_key : '' ) . " ) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_general_ci";
			$this->queries[] = $sql;
		}
	}

	public function alter( $Tables ){
		foreach ($Tables as $Table => $Columns) {
			foreach ($Columns as $name => $def) {
				$n_name = $name; $type = ''; $length = 0; $default = 0; $null = false; $after = ''; $ai = 0;

				if( !empty($def->name) ) $n_name = $def->name;
				if( !empty($def->type) ) $type = $def->type;
				if( !empty($def->length) ) $length = $def->length;
				if( !empty($def->null) ) $null = $def->null;
				if( !empty($def->default) ) $default = $def->default;
				if( !empty($def->after) && $name != $def->after ) $after = $def->after;
				if( !empty($def->auto_increment) ) $ai = $def->auto_increment;

				$this->queries[] = ("ALTER TABLE `$Table` CHANGE `$name` `$n_name` ".$type.( $type ? ( $length ? '('.$length.')' : ' unsigned' ) : '' ) ." " . ( $null ? 'NULL' : 'NOT NULL') . ( $ai ? ' AUTO_INCREMENT' : '' ) . " " . ( $default ? 'DEFAULT '.$default : '' ) . ( $after ? ' AFTER `'.$after.'`' : '' ) );

				if( !empty($def->primary) ){
					$this->queries[] = ("ALTER TABLE `$Table` ADD PRIMARY KEY(`$name`)");
				}
				if( !empty($def->unique) ){
					$this->queries[] = ("ALTER TABLE `$Table` ADD UNIQUE(`$name`)");
				}
				if( !empty($def->index) ){
					$this->queries[] = ("ALTER TABLE `$Table` ADD INDEX(`$name`)");
				}
			}
		}
	}

	public function insert( $Tables ){
		foreach ($Tables as $Table => $Columns) {
			foreach ($Columns as $name => $def) {
				$type = ''; $length = 0; $default = 0; $null = false; $after = ''; $ai = 0;

				if( empty($def->type) ){
					die( 'Type not defined' );
				}

				$type = $def->type;
				if( !empty($def->length) ) $length = $def->length;
				if( !empty($def->null) ) $null = $def->null;
				if( !empty($def->default) ) $default = $def->default;
				if( !empty($def->after) && $name != $def->after ) $after = $def->after;
				if( !empty($def->auto_increment) ) $ai = $def->auto_increment;

				$this->queries[] = ("ALTER TABLE `$Table` ADD `$name` ".$type.( $length ? '('.$length.')' : ' unsigned' )." " . ( $null ? 'NULL' : 'NOT NULL') . ( $ai ? ' AUTO_INCREMENT' : '' ) . " " . ( $default ? 'DEFAULT '.$default : '' ) . ( $after ? ' AFTER `'.$after.'`' : '' ) );

				if( !empty($def->primary) ){
					$this->queries[] = ("ALTER TABLE `$Table` ADD PRIMARY KEY(`$name`)");
				}
				if( !empty($def->unique) ){
					$this->queries[] = ("ALTER TABLE `$Table` ADD UNIQUE(`$name`)");
				}
				if( !empty($def->index) ){
					$this->queries[] = ("ALTER TABLE `$Table` ADD INDEX(`$name`)");
				}
			}
		}
	}

	/**
	 * @param array $Tables List of Tables to drop
	 */
	public function remove( $Tables ){
		foreach ($Tables as $Table => $Columns) {
			foreach ($Columns as $value) {
				$this->queries[] = ("ALTER TABLE `$Table` DROP `$value`");
			}
		}
	}

	/**
	 * @param array $Tables List of Tables to drop
	 */
	public function drop( $Tables ){
		foreach ($Tables as $value) {
			$this->queries[] = ("DROP TABLE `$value`");
		}
	}

	public function run(){
		foreach ($this->queries as $key => $value) {
			$time_b = floatval(explode(' ', microtime())[0]);

			$this->db->query($value);

			$time_a = floatval(explode(' ', microtime())[0]);

			$this->queries[$key] = [ $value, $this->db->error, round($time_a-$time_b, 2)*100 ];
		}
	}

	public function showQueriesState(){
		$html = "
<h1>Sql BreakDown</h1>
<table cellspacing='0' border='1' width='100%'>
	<tr>
		<th align='left'>SQL QUERY</th>
		<th align='left' width='150'>EXECUTION TIME</th>
	</tr>
		";
		foreach ($this->queries as $key => $value) {
			$html .= "<tr>
<td><font color='". ( $value[1] ? 'brown' : 'green' ) ."'>${value[0]}</font><br/>${value[1]}</td>
<td>${value[2]}ms</td>
			</tr>";
		}
		$html .= "
</table>
<style>
	body {
		padding: 0;
		margin: 0;
		font-family: 'Tahoma';
	}
	table {
		border-spacing: 0;
		width: 100%;
		max-width: 100%;
	}
	table td, table th {
		border: none !important;
		padding: 1em !important;
	}
	table tr:nth-child(even){
		background: pink;
	}
	table tr {
		border: 1px solid #333 !important;
		padding: 0 !important;
		background: #f2f2f2;
	}
</style>
		";
		echo $html;
	}
}

if( isset($_GET['f']) ){
	Framework::boot( __DIR__.'../' );
	$Migration = new Migrate( $_GET['f'] );
}