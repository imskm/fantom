<?php

namespace Fantom;

/**
* Base calss
* handles dynamic getting and setting of properties
*/
class Base
{
	protected $data = array();

	public function __get($key)
	{
		if (! array_key_exists($key, $this->data)) {
			throw new \Exception("Trying to access undefined property {$key}");
		}

		return $this->data[$key];
	}

	public function __set($key, $value)
	{
		$this->data[$key] = $value;
		return;
	}

	public function __isset($key)
	{
		return array_key_exists($key, $this->data);
	}

	public function __unset($key)
	{
		unset($this->data[$key]);
	}

	public function exist($key)
	{
		return array_key_exists($key, $this->data);
	}

	protected function allProperties()
	{
		if (! $this->data) {
			return [];
		}

		return $this->data;
	}

	protected function massStore(array $properties)
	{
		foreach ($properties as $property) {
			foreach ($property as $key => $value) {
				$this->data[$key] = $value;
			}
		}
	}
	
}