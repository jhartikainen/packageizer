<?php
class CU_Archive_Phar implements CU_Archive_Interface 
{
	/**
	 * @var Phar
	 */
	protected $_phar = null;
	
	public function __construct($path)
	{
		if(!extension_loaded('phar'))
			throw new RuntimeException('Phar extension is unavailable');
			
		$this->_phar = new Phar($path);
	}
	
	public function addFile($path, $localName = '')
	{
		try 
		{
			if($localName == '')
				$this->_phar->addFile($path);
			else
				$this->_phar->addFile($path, $localName);
		}
		catch(PharException $e)
		{
			throw new RuntimeException('File add failed: ' . (string)$e);
		}
	}
	
	public function close()
	{
		//unnecessary
	}
	
	public function getResource()
	{
		return $this->_phar;
	}
}