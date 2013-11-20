<?php

namespace Espo\Core;

class Container
{

	private $data = array();


	/**
     * Constructor
     */
    public function __construct()
    {

    }

    
    public function get($name)
    {
    	if (empty($this->data[$name])) {
    		$this->load($name);
    	}
    	
    	return $this->data[$name];
    }

    private function load($name)
    {
    	$loadMethod = 'load' . ucfirst($name);
    	if (method_exists($this, $loadMethod)) {
    		$this->$loadMethod();
    	} else {
            //external loader class \Espo\Core\Loaders\<className> or \Custom\Espo\Core\Loaders\<className> with load() method
			$className = '\Espo\Custom\Core\Loaders\\'.ucfirst($name);
            if (!class_exists($className)) {
            	$className = '\Espo\Core\Loaders\\'.ucfirst($name);
            }

			if (class_exists($className)) {
            	 $loadClass = new $className($this);
				 $this->data[$name] = $loadClass->load();
			}
    	}

		// TODO throw an exception
    	return null;
    }


    private function loadSlim()
    {
        $this->data['slim'] = new \Slim\Slim();
    }


	private function loadFileManager()
    {
    	$this->data['fileManager'] = new \Espo\Core\Utils\File\Manager(
			(object) array(
				'defaultPermissions' => (object)  array (
				    'dir' => '0775',
				    'file' => '0664',
				    'user' => '',
				    'group' => '',
			  ),
			)
		);
    }

	private function loadConfig()
    {
    	$this->data['config'] = new \Espo\Core\Utils\Config(
			$this->get('fileManager')
		);
    }

	private function loadLog()
    {
    	$this->data['log'] = new \Espo\Core\Utils\Log(
			$this->get('fileManager'),
			$this->get('output'),
			$this->get('resolver'),
			(object) array(
				'options' => $this->get('config')->get('logger'),
				'datetime' => $this->get('datetime')->getDatetime(),
			)
		);
    }

	private function loadOutput()
    {
    	$this->data['output'] = new \Espo\Core\Utils\Api\Output(
			$this->get('slim')
		);
    }

	private function loadMetadata()
    {
    	$this->data['metadata'] = new \Espo\Core\Utils\Metadata(
			$this->get('entityManager'),
			$this->get('config'),
			$this->get('fileManager'),
			$this->get('uniteFiles')
		);
    }


	private function loadLayout()
    {
    	$this->data['layout'] = new \Espo\Core\Utils\Layout(
			$this->get('config'),
			$this->get('fileManager'),
			$this->get('metadata')
		);
    }

	private function loadResolver()
    {
    	$this->data['resolver'] = new \Espo\Core\Utils\Resolver(
			$this->get('metadata')
  		);
    }

	private function loadDatetime()
    {
    	$this->data['datetime'] = new \Espo\Core\Utils\Datetime(
			$this->get('config')
		);
    }    

	private function loadUniteFiles()
    {
       	$this->data['uniteFiles'] = new \Espo\Core\Utils\File\UniteFiles(
			$this->get('fileManager'),
            (object) array(
				'unsetFileName' => $this->get('config')->get('unsetFileName'),
				'defaultsPath' => $this->get('config')->get('defaultsPath'),
			)
		);
    }

	private function loadUser()
    {
       	$this->data['user'] = new \Espo\Core\Utils\User(
			$this->get('entityManager'),
			$this->get('config')
		);
    }

}
