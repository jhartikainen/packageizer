<?php
class App_FileDataLoader
{
	private static $_instance;
	
	private function __construct()
	{
	}

	public static function getInstance()
	{
		if(!self::$_instance)
			self::$_instance = new self;
			
		return self::$_instance;
	}
	
	/**
	 * Load file data from xml file
	 *
	 * @param string $file
	 * @return App_FileData
	 */
	public static function load($file)
	{
		$loader = self::getInstance();
		
		return $loader->loadXml($file);
	}

	public function loadXml($file)
	{
		$xml = simplexml_load_file($file);
		
		$fileData = new App_FileData();
		
		foreach($xml->file as $file)
		{
			$fo = new stdClass();
			$fo->name = (string)$file['name'];
			
			$fo->requires = array();
			foreach($file->requires->require as $require)
				$fo->requires[] = (string)$require;
				
			$fo->interfaces = array();
			foreach($file->interfaces as $interface)
				$fo->interfaces[] = (string)$interface;
				
			$fo->classes = array();
			foreach($file->classes->class as $class)
			{
				$co = new stdClass();
				$co->name = (string)$class['name'];
				$co->extends = (string)$class['extends'];
				$co->implements = array();
				foreach($class->implements as $impl)
					$co->implements[] = (string)$impl;
					
				$fo->classes[] = $co;
			}
			
			$fileData->add($fo);
		}
		
		return $fileData;
	}
	
}