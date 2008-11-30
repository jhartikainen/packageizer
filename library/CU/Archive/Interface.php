<?php
interface CU_Archive_Interface
{
	public function addFile($path, $localName = '');
	public function getResource();
	public function close();
}