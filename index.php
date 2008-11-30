<?php
error_reporting(E_ALL|E_STRICT);
define('APP_PATH','.');

date_default_timezone_set('Europe/Helsinki');

set_include_path('.' .
	PATH_SEPARATOR . APP_PATH . '/library' . 
	PATH_SEPARATOR . APP_PATH . '/application/models/' . 
	PATH_SEPARATOR . get_include_path());
	
require_once 'Zend/Loader.php';

Zend_Loader::registerAutoload();


Zend_Layout::startMvc(array('layoutPath' => APP_PATH .'/application/layouts'));
$fc = Zend_Controller_Front::getInstance();

$router = $fc->getRouter();
$router->addRoute('library', new Zend_Controller_Router_Route(
	'library/:library', array(
		'controller' => 'library',
		'action' => 'package',
		'module' => 'default'
	)
));

$router->addRoute('library-action', new Zend_Controller_Router_Route(
	'library/:library/:action', array(
		'controller' => 'library',
		'module' => 'default'
	)
));

$router->addRoute('library-dependencies', new Zend_Controller_Router_Route(
	'library/:library/dependencies/:class', array(
		'action' => 'dependencies',
		'controller' => 'library',
		'module' => 'default'
	)
));

$router->addRoute('library-package', new Zend_Controller_Router_Route(
	'library/:library/package/:package/:parent', array(
		'action' => 'package',
		'controller' => 'library',
		'module' => 'default',
		'parent' => ''
	)
));

$router->addRoute('library-packet', new Zend_Controller_Router_Route(
	'library/:library/packet/:class/:format', array(
		'action' => 'packet',
		'controller' => 'library',
		'module' => 'default'
	)
));

$router->addRoute('library-packet-post', new Zend_Controller_Router_Route(
	'library/:library/packet/', array(
		'action' => 'packet',
		'controller' => 'library',
		'module' => 'default'
	)
));


$fc->setBaseUrl('/pack')
   ->throwExceptions(true)
   ->setParam('disableOutputBuffering',true)
   ->returnResponse(true)
   ->addModuleDirectory(APP_PATH . '/application/modules'); 

$response = $fc->dispatch();

$response->sendResponse();
