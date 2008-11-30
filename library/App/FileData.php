<?php
class App_FileData
{
	private $_data = array();
	
	public function __construct()
	{
	}
	
	public function add($o)
	{
		$this->_data[] = $o;
	}
	
	public function getFile($filename)
	{
		foreach($this->_data as $file)
		{
			if($file->name == $filename)
				return $file;
		}
		
		return null;
	}
	
	public function getFileByClass($className)
	{
		foreach($this->_data as $file)
		{
			foreach($file->classes as $class)
			{
				if($class->name == $className)
					return $file;
			}
		}
		
		return null;
	}
	
	
	public function getClasses()
	{
		$classes = array();
		
		foreach($this->_data as $file)
		{
			foreach($file->classes as $class)
				$classes[] = $class; 
		}
		
		return $classes;
	}
}