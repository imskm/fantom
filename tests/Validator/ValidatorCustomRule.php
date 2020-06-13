<?php

namespace Fantom\Tests\Validator;

use Fantom\Validation\Validator;

class ValidatorCustomRule extends Validator
{
	public function validateCustom($field, $data)
	{
		$_GET[$field] = $data;
		$this->validate("GET", [
				$field => "custom",
		]);
	}

	public function custom_rule($field, $data)
	{
		if ($data != "TEST_DATA") {
			$this->setError($field, __FUNCTION__, "Test error message");
			return false;
		}

		return true;
	}
}
