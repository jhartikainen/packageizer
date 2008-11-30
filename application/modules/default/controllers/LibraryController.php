<?php
class LibraryController extends Zend_Controller_Action 
{
    private $_requiredFiles = array();
    private $_libraryId = '';
    public function init()
    {
    	$this->_libraryId = $this->_getParam('library');
    	if(empty($this->_libraryId))
    		$this->_helper->redirector->goto('index','index');
    		
    	$this->view->library = $this->_libraryId;

		$this->_helper->contextSwitch()
		     ->addActionContext('package', 'json')
		     ->addActionContext('dependencies', 'json')
		     ->setAutoJsonSerialization(false)
		     ->initContext();
    }
    
    public function indexAction() 
    {
		$this->_forward('package');
	}

	public function packageAction()
	{
		$package = $this->_getParam('package');
		$parent = $this->_getParam('parent');
		$file = 'cache/files_' . $this->_libraryId . '.xml';
    	$data = App_FileDataLoader::load($file);

		$libs = simplexml_load_file('data/libraries.xml');
		
		$libraries = array();
		foreach($libs->library as $lib)
		{
			if((string)$lib['id'] == $this->_libraryId)
			{
				$this->view->libraryName = (string)$lib['name'];
				break;
			}
		}
		
	
		$packageData = require 'cache/packages_' . $this->_libraryId . '.php';
		if(!empty($package))
		{
			if(empty($parent))
			{
				$packages = $packageData[$package]['packages'];
				$classes = $packageData[$package]['classes'];
			}
			else
			{
				$packages = $packageData[$parent]['packages'][$package]['packages'];
				$classes = $packageData[$parent]['packages'][$package]['classes'];
			}
		}
		else
		{
			$packages = $packageData;
			$classes = array();
		}

		$this->view->packages = $packages;
    	$this->view->classes = $classes;
		$this->view->package = $package;
    }
  
    public function dependenciesAction()
    {
    	$file = 'cache/files_' . $this->_libraryId . '.xml';
    	$data = App_FileDataLoader::load($file);
    	$file = $data->getFileByClass($this->_getParam('class'));
    	
    	$this->_requiredFiles[] = $file->name;
    	$this->_getRequires($file, $data);

		for($i = 0, $len = count($this->_requiredFiles); $i < $len; $i++)
		{
			$file = $this->_requiredFiles[$i];

			if(strpos($file, 'Zend') !== 0)
				continue;

			$reqs[] = $file;
		}
    	
    	$this->view->requires = $reqs;
    	$this->view->class = $this->_getParam('class');
    	
    }
    
    public function packetAction()
    {
    	$format = $this->_getParam('format');
    	$formats = array('zip','phar');
    	
    	if(!in_array($format, $formats))
    		$this->_helper->redirector->goto('index','index');
    	
    	if(!extension_loaded($format))
    		$this->_helper->redirector->goto('index','index');
    		
    	$class = $this->_getParam('class');
		$classes = array();
		$classesParam = $this->_getParam('classes');
		if(!empty($classesParam))
			$classes = explode(',', $classesParam);
		else
			$classes = array($class);
		
    	$cached = sprintf('cache/%s.%s', md5(implode(',', $classes) . $this->_libraryId), $format);
    	
    	if(!file_exists($cached))
    	{
    		$file = 'cache/files_' . $this->_libraryId . '.xml';
    		$data = App_FileDataLoader::load($file);
    		$archive = CU_Archive::create($format, $cached);
			foreach($classes as $class)
			{
	    		$file = $data->getFileByClass($class);
    	
				$library = $this->_loadLibrary();
    		
	    		$this->_requiredFiles[] = $file->name;
	    		$this->_getRequires($file, $data);
    	
    		
				foreach($this->_requiredFiles as $require)
	    			$archive->addFile($library['path'] . '/' . $require, $require);
			}

    		$archive->close();
    	}
    	
		$filename = implode('-', $classes);
		if(strlen($filename) > 100)
			$filename = substr($filename, 0, 100);

    	header('Content-Type: application/' . $format);
		header('Content-Length: ' . filesize($cached));
    	header('Content-Disposition: attachment; filename="' . $filename . '.' . $format . '"');		
    	readfile($cached);
    	exit;
    }
    
    private function _loadLibrary()
    {
    	$libs = simplexml_load_file('data/libraries.xml');
		
    	$library = null;
		foreach($libs->library as $lib)
		{
			if($lib['id'] == $this->_libraryId)
			{
				$library = $lib;
				break;
			}
		}
		
		if($library == null)
			throw new Exception('Error loading library data');
			
		return $library;
    }
    
    private function _getRequires($file,$data)
    {
    	if(!$file)
    		return;
    	foreach($file->requires as $require)
    	{
    		if(in_array($require, $this->_requiredFiles))
    			continue;
    			
    		$this->_requiredFiles[] = $require;
    		$requireFile = $data->getFile($require);
    		$this->_getRequires($requireFile, $data);
    	}
    }
    
    public function mapAction()
    {
    	$library = $this->_loadLibrary();
    	
	    $mapper = new App_FileMapper((string)$library['path']);
    	$mapper->setStripFromPath((string)$library['path'] . '/');
		$mapper->map(true);
		$mapped = $mapper->getData();
		
		$this->view->files = $mapped;

		if(!file_exists(APP_PATH . '/cache/packages_' . $this->_libraryId . '.php'))
		{
			$packs = $this->_getPackages($mapped);
			file_put_contents(APP_PATH . '/cache/packages_'. $this->_libraryId . '.php', '<?php return ' . var_export($packs, true) . ';');
		}
		
		if(!file_exists(APP_PATH .'/cache/files_' . $this->_libraryId . '.xml'))
		{
			$view = new Zend_View();
			$view->files = $mapped;
			$view->setScriptPath(APP_PATH . '/data');
			$data = $view->render('files_xml.phtml');
			file_put_contents(APP_PATH .'/cache/files_' . $this->_libraryId . '.xml', $data);
		}
    }

	private function _getPackages($files)
	{
		$packageTree = array();
		$packages = array();
		foreach($files as $file)
		{
			foreach($file['classes'] as $className => $class)
			{
				$doc = $class['docBlock'];
				$package = $subPackage = '';
				
				if(isset($doc['package']))
					$package = $doc['package'];
				if(isset($doc['subpackage']))
					$subPackage = $doc['subpackage'];

				if(empty($package))
					$package = 'default';
					
				if(!isset($packageTree[$package]))
				{
					$packageTree[$package] = array(
						'packages' => array(),
						'classes' => array()
					);
				}

				if(!empty($subPackage))
				{
					if(!isset($packageTree[$package]['packages'][$subPackage]))
					{
						$packageTree[$package]['packages'][$subPackage] = array(
							'packages' => array(),
							'classes' => array()
						);
					}
						
					$packageTree[$package]['packages'][$subPackage]['classes'][] = $className;
				}
				else
				{
					$packageTree[$package]['classes'][] = $className;
				}
			}
		}

		return $packageTree;
	}
}
