<?php

namespace SPhp\Framework;

class Template {
	const SYM = '!';
	const TSYM = '!';
	
	public $name = '';
	protected $Template_Data;
	protected $writeto;
	protected $GLOBALS;
	protected $string = '';

	public function __construct( $name='' ){
		if( $name ){
			$this->name = $name;
			$name = __BASE."/static/templates/$name.html";
			if( !file_exists($name) ) {
				Error::die([[
					1,
					"Template file not found ($name)",
					'TEMPLATE_NOT_FOUND'
				]]);
			} else {
				$this->string = file_get_contents($name);
			}
		}

		$this->Template_Data = array(
			'_GET' => $_GET,
			'_POST' => $_POST,
			'_SITE' => array(
				'__BASE' => __BASE,
				'__URL' => __URL,
				'__HOME' => __HOME
			),
		);
		$this->writeto = array();
		$this->GLOBALS = array();
	}
	
	public function parse( $name_or_data, $data=array() ){
		if( !( isset($this) && get_class($this)==__CLASS__ ) ){
			$tmp = new Template( $name_or_data );
			return $tmp->parse( $data );
		}

		return $this->parse_string( $this->string, $name_or_data );
	}

	public function parse_string( $string, $data=array() ){
		if( !( isset($this) && get_class($this)==__CLASS__ ) ){
			$tmp = new Template();
			return $tmp->parse_string( $string, $data );
		}

		$this->Template_Data = array_merge( $this->Template_Data, $data );
		$this->string = $string;

		$command = false;
		$command_query = '';

		$re_image = '';
		$string = preg_split('//', $string);
		$just_run = false;

		foreach ($string as $pos => $char) {
			$write = '';

			if( !empty($this->GLOBALS['verbatim']) ) {
				$write = $char;

				if( substr($this->string, $pos, 8)==Template::SYM.Template::SYM.'endfor' ){
					$for_block = strpos($this->GLOBALS['_parsing_for'][0], Template::SYM.Template::SYM.'for ')!==FALSE;
					$end_for = isset($this->GLOBALS['_parsing_for'][0]) && preg_match('/(?:.*!!for [a-zA-Z_@][\w_@:,]*!!.*!!endfor!!.*)+/sm', $this->GLOBALS['_parsing_for'][0], $matches);

					#print_r($matches);
					#echo "\nCHECKING IF FOR_BLOCK CONTAINS FOR??\n###############################\n".$this->GLOBALS['_parsing_for'][0]."\n################################\n";
					#var_dump(($end_for));

					if( !$for_block || ($for_block && $end_for) ){
						unset($this->GLOBALS['verbatim']);
					}
				}
			} else if( $char==Template::TSYM && $string[$pos-1]==Template::TSYM && $command ){
				#C echo "$char $pos(RUNNIG COMMAND)\n";
				$write = $this->doQuick( $command_query );
				$command = false;
				$just_run = true;
			} else if( $char==Template::SYM && $string[$pos-1]==Template::SYM && !$command && !$just_run ){
				#C echo "$char $pos(COMMAND MODE)\n";
				$command = true;
			} else if( ($char==Template::SYM && $string[$pos+1]!=Template::SYM) || ($char==Template::TSYM && $string[$pos+1]!=Template::TSYM) ) {
				if( $command ){
					$command_query .= $char;
				} else {
					$write = $char;
				}
			} else if( $char==Template::SYM || $char==Template::TSYM ) {
				if( $just_run ){
					$just_run = false;
					$command_query = '';
				}
				# ... do nothing ... #
			} else if( $command ){
				#C echo "$char $pos(COMMAND QUERY)\n";
				$command_query .= $char;
			} else {
				#C echo "$char $pos(NORMAL)\n";
				$write = $char;
				$command = '';

				$command = false;
				$command_query = '';
				$just_run = false;
			}

			if( sizeof($this->writeto) ){
				#C echo "SEEN ($char) WRITING TO (".$this->writeto[0].")\n";

				if( is_array($this->GLOBALS[$this->writeto[0]]) ){
					$this->GLOBALS[$this->writeto[0]][0] .= $write;
				} else {
					$this->GLOBALS[$this->writeto[0]] .= $write;
				}
			} else {
				$re_image .= $write;
			}
		}

		if( isset($this->GLOBALS['extends_template']) && empty($this->GLOBALS['finished_extending']) ){
			$this->GLOBALS['finished_extending'] = true;
			return $this->GLOBALS['extends_template']->parse( $this->GLOBALS['extends_template_data'] );
		}
		return ($re_image);
	}

	public function doQuick( $string ){
		$len = strlen($string);
		$string = trim($string);

		$data_path = [];

		if( $string && $string[0]=='-'&&$string[1]=='-' && $string[$len-2]=='-'&&$string[$len-1]=='-' ){
			/*
			 * String is comment: return nothing
			 */
			return '';
		} else if( strpos($string, 'include=')===0 ){
			/*
			 * String is include
			 */
			$string = str_replace('include=', '', $string);
			$string = new Template( $string );
			return $string->parse( $this->Template_Data );
		} else if( strpos($string, 'uses=')===0 ){
			/*
			 * String is uses
			 */
			$string = str_replace('uses=', '', $string);
			$this->GLOBALS['extends_template'] = new Template( $string );
			$this->GLOBALS['extends_template_data'] = array();
			return '';
		} else if( strpos($string, 'var ')===0 ){
			/*
			 * String is var
			 */
			$string = explode('=', str_replace('var ', '', $string), 2);
			eval('$this->Template_Data["'.$string[0].'"] = '.$string[1].';');
			return '';
		} else if( strpos($string, 'block ')===0 || strpos($string, 'endblock')===0 ){
			/*
			 * String is block: return nothing
			 */
			if( isset($this->GLOBALS['_parsing_block']) && strpos($string, 'block ')===0 ){
				Error::die([[
					0,
					'A block tag inside another blog tag is impossible',
					'BLOCK_IN_BLOCK'
				]]);
			}

			if( !isset($this->GLOBALS['_parsing_block']) ) {
				$this->GLOBALS['_parsing_block'] = '';
				$this->GLOBALS['_parsing_block_name'] = str_replace('block ', '', $string);
				array_unshift($this->writeto, '_parsing_block');
			} else {
				$name = $this->GLOBALS['_parsing_block_name'];
				$content = $this->GLOBALS['_parsing_block'];

				#C echo "Built block (".$name.")\n#######################\n".$content."\n###############################\n";

				$this->GLOBALS['extends_template_data'][ "block=$name" ] = $content;

				unset($this->GLOBALS['_parsing_block']);
				unset($this->GLOBALS['_parsing_block_name']);
				array_shift($this->writeto);
			}

			return '';
		} else if( strpos($string, 'for ')===0 || strpos($string, 'endfor')===0 ){
			/*
			 * String is for: return nothing
			 */
			if( !isset($this->GLOBALS['_parsing_for']) ){
				$this->GLOBALS['_parsing_for'] = [];
				$this->GLOBALS['_parsing_for_name'] = [];
			}
			#C echo("CALLING FOR ($string) \n");

			if( strpos($string, 'for ')===0 ){
				$this->GLOBALS['verbatim'] = true;

				array_unshift($this->GLOBALS['_parsing_for'], '');
				array_unshift($this->GLOBALS['_parsing_for_name'], str_replace('for ', '', $string));
				array_unshift($this->writeto, '_parsing_for');
			} else {
				$name = $this->GLOBALS['_parsing_for_name'][0];
				$content = $this->GLOBALS['_parsing_for'][0];
				$loop_content = '';

				#C echo "Parsing for block (".$name.")\n#######################\n".$content."\n###############################\n";

				if( strpos($name, 'range:')===0 ){
					$_name = preg_split('/:|,/', $name);
					$min = 0;
					$max = $_name[1];
					if( !empty($_name[2]) ){
						$min = $max;
						$max = $_name[2];
					}

					if( intval($min)<0 && intval($max)<0 ){
						Error::die([[
							0,
							'range function requires integers ('.$name.') in ('.$this->name.'.html)',
							'TEMPLATE_FOR_INVALID_RANGE'
						]]);
					}

					for ($i=$min; $i < $max+1; $i++) { 
						$loop_content .= (new Template())->parse_string( str_replace(Template::SYM.Template::SYM.'index'.Template::TSYM.Template::TSYM, $i, $content), $this->Template_Data );
					}
				} else if( preg_match('/[a-zA-Z_@][\w_@]*/', $name) ){

					$name = explode('.', $name);
					$tmp = $this->Template_Data;
					foreach ($name as $value) {
						if( isset($tmp[$value]) ){
							$tmp = $tmp[$value];
						} else {
							Error::die([[
								1,
								'Undefined variable ('.implode('.', $name).') in for block ('.$this->name.'.html)',
								'TEMPLATE_FOR_UNDEFINED_VARIABLE'
							]]);
						}
					}

					if ( !is_array($tmp) ) {
						Error::die([[
							1,
							'For block argument must be an iterable ('.implode('.', $name).')',
							'TEMPLATE_FOR_UNITERABLE_VARIABLE'
						]]);
					}

					foreach ( $tmp as $index=>$value ){
						$loop_content .= (new Template())->parse_string( $content, array_merge( $this->Template_Data, array( 'this' => $value, 'ithis' => $index ) ) );
					}

				} else {
					Error::die([[
						0,
						'Syntax error on block (for '.$name.')',
						'TEMPLATE_FOR_SYNTAX_ERROR'
					]]);
				}

				array_shift($this->GLOBALS['_parsing_for']);
				array_shift($this->GLOBALS['_parsing_for_name']);
				array_shift($this->writeto);

				return $loop_content;
			}
			return '';
		} else if( strpos($string, 'if ')===0 || strpos($string, 'endif')===0 ){
			/*
			 * String is if
			 */
			if( !isset($this->GLOBALS['_parsing_if']) ){
				$this->GLOBALS['_parsing_if'] = [];
				$this->GLOBALS['_parsing_if_name'] = [];
			}

			if( strpos($string, 'if ')===0 ){
				array_unshift($this->GLOBALS['_parsing_if'], '');
				array_unshift($this->GLOBALS['_parsing_if_name'], str_replace('if ', '', $string));
				array_unshift($this->writeto, '_parsing_if');
			} else {
				$name = $this->GLOBALS['_parsing_if_name'][0];
				$content = $this->GLOBALS['_parsing_if'][0];

				#C echo "Parsing if condition (".$name.")\n#######################\n".$content."\n###############################\n";

				preg_match_all('/([\'][^\']*[\']|["][^"]*["])/', $name, $matches);
				$name = preg_replace('/([\'][^\']*[\']|["][^"]*["])/', '*********', $name);
				$name = preg_replace('/\$/', '', $name);
				$name = preg_replace('/([a-zA-Z_@][\w_@]*)/', '$this->Template_Data[\'$1\']$2', $name);
				foreach ($matches[0] as $value) {
					$name = preg_replace('/\*\*\*\*\*\*\*\*\*/', $value, $name, 1);
				}
				eval('$name = ('.preg_replace('/([^=])=([^=])/', '$1==$2', $name).');');

				array_shift($this->GLOBALS['_parsing_if']);
				array_shift($this->GLOBALS['_parsing_if_name']);
				array_shift($this->writeto);

				if( $name ){
					return $content;
				}
			}
			return '';
		} else if( isset($this->Template_Data[trim($string)]) ){
			/*
			 * String is root-level data
			 */
			$data = $this->Template_Data[trim($string)];
			if( is_array($data) ){
				$data = print_r($data, TRUE);
			}

			return strval($data);
		} else if( preg_match('/^([\w_]+)(\.[\w_]+)+$/', $string, $data_path) ){
			/*
			 * String is nested data
			 */
			$string = explode('.', $string);
			$tmp = $this->Template_Data;
			foreach ($string as $value) {
				if( isset($tmp[$value]) ){
					$tmp = $tmp[$value];
				} else {
					return '***';
				}
			}
			return $tmp;
		} else {
			/*
			 * String is undefined
			 */
			try {
				return eval("return $string;");
			} catch( \Throwable $e ){
				Error::die([[
					0,
					"Error parsing command (".$e->getMessage().")",
					'TEMPLATE_COMMAND_ERROR'
				]]);
			}
		}
	}

	public function __( $name, $data=[] ){
		global $Response;
		$Response->__( Template::parse($name, $data) );
	}
}