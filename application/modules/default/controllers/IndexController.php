<?php
class IndexController extends Zend_Controller_Action 
{
	public function indexAction()
	{
		$libs = simplexml_load_file('data/libraries.xml');
		
		$libraries = array();
		foreach($libs->library as $lib)
		{
			$libraries[] = array(
				'name' => (string)$lib['name'],
				'id' => (string)$lib['id']
			);
		}
		
		$this->view->libraries = $libraries;
	}
}