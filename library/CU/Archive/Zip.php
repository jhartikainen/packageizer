<?php
class CU_Archive_Zip implements CU_Archive_Interface 
{
	/**
	 * @var ZipArchive
	 */
	protected $_zip = null;
	
	public function __construct($path)
	{
		if(!extension_loaded('zip'))
			throw new RuntimeException('zip extension is not available');
			
		$this->_zip = new ZipArchive();
		
		if(!file_exists($path))
			$this->_zip->open($path, ZipArchive::CREATE);
		else
			$this->_zip->open($path);
	}
	
	public function addFile($file, $localName = '')
	{
		if($localName == '')
			$this->_zip->addFile($file);
		else
			$this->_zip->addFile($file, $localName);
	}
	
	public function close()
	{
		$this->_zip->close();
	}
	
	public function getResource()
	{
		return $this->_zip;
	}
}