<?php

/**
* Loader class
*/
class Loader {
	protected $registry;
	protected $model;

	/**
	 * Constructor
	 *
	 * @param	object	$registry
 	*/
	public function __construct($registry, $model) {
		$this->registry = $registry;
		$this->model = $model;
	}

	/**
	 * 
	 *
	 * @param	string	$route
 	*/	
	public function model($route) {
		$this->model = $route;
	}

	public function getModel()
    {
        return $this->model;
    }

    public function controller($type) {
		return $type;
    }
}