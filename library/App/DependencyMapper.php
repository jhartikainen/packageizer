<?php
class App_DependencyMapper
{
	private $_filePath;
	private $_includePath;
	private $_files;
	
	public function __construct($filePath)
	{
		$this->_filePath = $filePath;
	}
	
	public function setIncludePath($path)
	{
		$this->_includePath = $path;
	}
	
	
	public function map()
	{
		$this->_files = array();
		$this->_mapFile($this->_filePath);
		return $this;
	}
	
	private function _mapFile($path)
	{
		$data = $this->_parseFile($this->_includePath . '/' . $path);
		$this->_files[$path] = $data;
		
		foreach($data['requires'] as $require)
		{
			if(strpos($require, '.php') !== false && !array_key_exists($require, $this->_files))
				$this->_mapFile($require);
		}
	}
	
	private function _parseFile($filename)
	{
		echo "Opening $filename <br>";flush();
		$data = file_get_contents($filename);
		
		$tokenizer = new App_Tokenizer();
		$tokenizer->parse($data);
		
		$tokens = $tokenizer->getTokenData();
		
		$fileData = array(
			'classes' => array(),
			'requires' => array(),
			'interfaces' => array()
		);
		
		$lastClass = '';
		foreach($tokens as $token)
		{
			switch($token['token'])
			{
				case T_REQUIRE:
				case T_REQUIRE_ONCE:
					$requiredFile = trim($token['data'], '\'"');
					if(!in_array($requiredFile, $fileData['requires']))
						$fileData['requires'][] = $requiredFile;
					break;
				
				case T_CLASS:
					$fileData['classes'][$token['data']] = array(
						'extends' => '',
						'implements' => array()
					);
					$lastClass = $token['data'];
					break;
					
				case T_INTERFACE:
					$fileData['interfaces'][] = $token['data'];
					break;
				
				case T_EXTENDS:
					if($lastClass == '')
						throw new Exception('Extends can not come before any class declarations!');
						
					$fileData['classes'][$lastClass]['extends'] = $token['data'];
					break;
					
				case T_IMPLEMENTS:
					if($lastClass == '')
						throw new Exception('Implements can not come before any class declarations!');
					
					$fileData['classes'][$lastClass]['implements'][] = $token['data'];
					break;
			}
		}
		
		return $fileData;
	}
}