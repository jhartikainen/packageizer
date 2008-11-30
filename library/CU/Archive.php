<?php
class CU_Archive
{
	public static function create($type, $path)
	{
		$class = 'CU_Archive_' . ucfirst($type);
		
		return new $class($path);
	}
}