<?php
class App_FileMapper
{
	private $_filePath;
	private $_stripFromPath;
	private $_files;
	
	public function __construct($filePath)
	{
		$this->_filePath = $filePath;
	}
	
	public function setStripFromPath($path)
	{
		$this->_stripFromPath = $path;
	}
	
	
	public function map($recursive = false)
	{
		$this->_files = array();
		$this->_mapPath($this->_filePath, $recursive);
		return $this;
	}
	
	private function _mapPath($path, $recursive)
	{
		$dir = dir($path);
		
		while(($item = $dir->read()) !== false)
		{
			if($item == '.' || $item == '..')
				continue;
				
			$completePath = $path . '/' . $item;
			
			if(is_file($completePath) && strpos($item,'.php') !== false && !array_key_exists(str_replace($this->_stripFromPath, '',$completePath), $this->_files))
				$this->_parseFile($completePath);
			elseif(is_dir($completePath) && $recursive)
				$this->_mapPath($completePath, $recursive);
		}
		
		$dir->close();
	}
	
	
	private function _parseFile($filename)
	{
		echo "Opening $filename <br>";
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
		$docBlock = array();
		foreach($tokens as $token)
		{
			switch($token['token'])
			{
				case T_REQUIRE:
				case T_REQUIRE_ONCE:
					$requiredFile = trim($token['data'], '\'"');
					
					if(!in_array($requiredFile, $fileData['requires']))
						$fileData['requires'][] = str_replace($this->_stripFromPath, '', $requiredFile);
					break;
				
				case T_DOC_COMMENT:
					$docBlock = array();
					$matches = array();
					preg_match('/@package\s+(.*)/', $token['data'], $matches);
					if(count($matches) == 2)
						$docBlock['package'] = $matches[1];

					preg_match('/@subpackage\s+(.*)/', $token['data'], $matches);
					if(count($matches) == 2)
						$docBlock['subpackage'] = $matches[1];

					break;

				case T_CLASS:
					$fileData['classes'][$token['data']] = array(
						'extends' => '',
						'implements' => array(),
						'docBlock' => $docBlock
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
		
		$this->_files[ str_replace($this->_stripFromPath, '', $filename) ] = $fileData;
	}
	
	
	public function getData()
	{
		if(!is_array($this->_files))
			$this->map();
			
		return $this->_files;
	}
	
}
