<?php

class Aria_ServiceManager {

	private $services;

	protected function __construct() {
		$this->services;
	}

	#Virtual
	protected function getActualServiceName($name) {
		return $name;
	}

	public function getService($name) {
		if (!isset($this->services[$name])) {
			$name = basename($this->getActualServiceName($name));
			require_once strtr($name, '_', DIRECTORY_SEPARATOR) . '.class.php';
			$this->services[$name] = new $name($this);
		}
		return $this->services[$name];
	}

}
