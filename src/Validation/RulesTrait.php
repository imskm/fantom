<?php

namespace Fantom\Validation;

use Fantom\Database\Connector;

trait RulesTrait
{
	/**
	 * @var array $allowedRules   stores rules that will be allowed
	 */
	protected $allowedRules = [
		"alpha"				=> true,
		"alpha_dash"		=> true,
		"alpha_num"			=> true,
		"alpha_space"		=> true,
		"confirmed"			=> true,
		"date"				=> true,
		"date_equals"		=> true,
		"digits"			=> true,
		"email"				=> true,
		"exist"				=> true,
		"in"				=> true,
		"integer"			=> true,
		"max"				=> true,
		"min"				=> true,
		"numeric"			=> true,
		"optional"			=> true,
		"phone"				=> true,
		"required"			=> true,
		"size"				=> true,
		"unique"			=> true,
	];

	/**
	 * @var array $exceptionalRules  stores rules that are treaded exceptionally
	 */
	protected $exceptionalRules = [
		"optional"			=> true,
	];

	/**
	 * Numeric rules
	 */
	protected $numeric_rules = ['numeric', 'integer'];


	protected function isRuleAllowed($rule)
	{
		return array_key_exists($rule, $this->allowedRules);
	}

	protected function isRuleExceptional($rule)
	{
		return array_key_exists($rule, $this->exceptionalRules);
	}


	protected function alpha($field, $data)
	{
		$pattern = "/^[a-zA-Z]+$/";

		if(!preg_match($pattern, $data)) {
			$this->setError($field, __FUNCTION__, "$field should contain alphabets only.");
			return false;
		}

		return true;
	}

	protected function alpha_dash($field, $data)
	{
		$pattern = "/^[a-zA-Z-]+$/";
		
		if(!preg_match($pattern, $data)) {
			$this->setError($field, __FUNCTION__, "$field should contain alphabets and dashes only.");
			return false;
		}

		return true;
	}

	protected function alpha_num($field, $data)
	{
		$pattern = "/^[a-zA-Z0-9]+$/";
		
		if(!preg_match($pattern, $data)) {
			$this->setError($field, __FUNCTION__, "$field should contain alphabets and numbers only.");
			return false;
		}

		return true;
	}

	protected function alpha_space($field, $data)
	{
		$pattern = "/^[a-zA-Z ]+$/";
		
		if(!preg_match($pattern, $data)) {
			$this->setError($field, __FUNCTION__, "$field should contain alphabets and spaces only.");
			return false;
		}

		return true;
	}

	protected function confirmed($field, $data, $confirm)
	{
		$confirmWith = $this->getInputValue($confirm);

		if($data !== $confirmWith) {
			$this->setError($field, __FUNCTION__, "$field does not match with $confirm.");
			return false;
		}

		return true;
	}

	protected function date($field, $data)
	{
		if (!is_string($data) || is_numeric($data) || !strtotime($data)) {
			$this->setError($field, __FUNCTION__, "$data is invalid date");
			return false;
		}

		$date = date_parse($data);
        if (!checkdate($date['month'], $date['day'], $date['year'])) {
        	$this->setError($field, __FUNCTION__, "$data is invalid date");
			return false;
        }

		return true;
	}

	/**
	 * compares date for equality
	 * Note: Not perfect
	 */
	protected function date_equals($field, $data, $args)
	{
		// First check for valid date
		if (!is_string($data) || is_numeric($data) || !strtotime($data)) {
			$this->setError($field, __FUNCTION__, "$data is invalid date");
			return false;
		}

		$date = date_parse($data);
        if (!checkdate($date['month'], $date['day'], $date['year'])) {
        	$this->setError($field, __FUNCTION__, "$data is invalid date");
			return false;
        }

		// Intentionally used without try catch
		// If developer gave invalid date in $args, will throw exception
		$arg_date = new \DateTime($args);
		$interval = $arg_date->diff(new \DateTime($data));

		if ($interval->s !== 0) {
			$this->setError($field, __FUNCTION__, "$data does not match.");
			return false;
		}

		return true;
	}

	protected function digits($field, $data)
	{
		$pattern = "/^[0-9]+$/";
		
		if(!preg_match($pattern, $data)) {
			$this->setError($field, __FUNCTION__, "$field should be number.");
			return false;
		}

		return true;
	}

	protected function email($field, $data)
	{
		if (!filter_var($data, FILTER_VALIDATE_EMAIL)) {
			$this->setError($field, __FUNCTION__, "Email address is not valid.");
    		return false;
		}
		
		return true;
	}

	protected function exist($field, $data, $args)
	{
		// $args[0] => Table, $args[1] => column
		$parts = explode(",", $args);
		if (count($parts) !== 2) {
			throw new \Exception("Invalid argument to ".__FUNCTION__." rule.");
		}

		$sql = sprintf("SELECT 1 FROM %s WHERE %s = :%s", $parts[0], $parts[1], $parts[1]);
		if(!$this->dbQuickCheck($sql, ["$parts[1]" => $data])) {
			$this->setError($field, __FUNCTION__, "$data does not exist.");
			return false;
		}

		return true;
	}

	protected function in($field, $data, $args)
	{
		$lists = explode(",", $args);

		if(count($lists) === 0) {
			throw new \Exception("List of value is empty in the \"".__FUNCTION__."\" rule.");
		}

		if(!in_array($data, $lists)) {
			$this->setError($field, __FUNCTION__, "$field does not has given matching value.");
			return false;
		}

		return true;
	}

	protected function integer($field, $data)
	{
		$options = [
			'flags' => FILTER_FLAG_ALLOW_OCTAL, FILTER_FLAG_ALLOW_HEX
		];

		if (filter_var($data, FILTER_VALIDATE_INT, $options) === false) {
			$this->setError($field, __FUNCTION__, "$field is not integer.");
			return false;
		}

		return true;
	}

	protected function max($field, $data, $args)
	{
		// Returns the size that will be compared against $args
		// getSize() method takes care of couting lenth of data.
		$size = $this->getSize($field, $data);

		// If $size exceeds $args value then set error and return false
		if($size > (float) $args) {
			$this->setError($field, __FUNCTION__, "$field must not exceeds $args.");
			return false;
		}

		return true;
	}

	protected function min($field, $data, $args)
	{
		// Returns the size that will be compared against $args
		// getSize() method takes care of couting lenth of data.
		$size = $this->getSize($field, $data);

		// If $data is a number then compare it like number
		if((float) $size < (float) $args) {
			$this->setError($field, __FUNCTION__, "$field must be atleast $args.");
			return false;
		}

		return true;
	}

	protected function numeric($field, $data)
	{
		if(!is_numeric($data)) {
			$this->setError($field, __FUNCTION__, "$field should be number.");
			return false;
		}

		return true;
	}

	protected function optional($field, $data)
	{
		// If $data is empty then don't set error because it's optional
		if(trim($data) === '') {
			return false;
		}

		return true;
	}

	protected function phone($field, $data)
	{
		$pattern = "/^[0-9]{10}$/";

		if(!preg_match($pattern, $data)) {
			$this->setError($field, __FUNCTION__, "Phone number is not valid.");
			return false;
		}

		return true;
	}

	protected function required($field, $data)
	{
		$data = str_replace(" ", "", $data);

		if(!$data) {
			$this->setError($field, __FUNCTION__, "$field is required");
			return false;
		}

		return true;
	}

	protected function size($field, $data, $args)
	{
		// Returns the size that will be compared against $args
		// getSize() method takes care of couting lenth of data.
		$size = $this->getSize($field, $data);

		if ((float) $size !== (float) $args) {
			$this->setError($field, __FUNCTION__, "Size of $field should be equal to $args.");
			return false;
		}

		return true;
	}

	protected function unique($field, $data, $args)
	{
		// $args[0] => Table, $args[1] => column
		$parts = explode(",", $args);
		if (count($parts) !== 2) {
			throw new \Exception("Invalid argument to ".__FUNCTION__." rule.");
		}

		$sql = sprintf("SELECT 1 FROM %s WHERE %s = :%s", $parts[0], $parts[1], $parts[1]);
		if($this->dbQuickCheck($sql, ["$parts[1]" => $data])) {
			$this->setError($field, __FUNCTION__, "$field $data is not unique.");
			return false;
		}

		return true;
	}


	/**
	 * Helper method for quick check existence of data in db
	 */
	protected function dbQuickCheck($sql, array $params = [])
	{
		$db = Connector::getConnection();
		$st = $db->prepare($sql);

		foreach ($params as $param => $value) {
			if (is_null($value)) {
				$st->bindValue(":$param", $value, \PDO::PARAM_NULL);
			} else if (is_bool($value)) {
				$st->bindValue(":$param", $value, \PDO::PARAM_BOOL);
			} else if (is_numeric($value) && is_int($value + 0)) {
				$st->bindValue(":$param", $value, \PDO::PARAM_INT);
			} else {
				$st->bindValue(":$param", $value, \PDO::PARAM_STR);
			}
		}

		return $st->execute() && (bool)$st->fetch();
	}

	/**
	 * If the $field has any numeric rules then size is $data itself
	 * else size is count of characters
	 *
	 * @return int | float
	 */
	protected function getSize($field, $data)
	{
		$has_numeric = $this->fieldHasRule($field, $this->numeric_rules);
		if ($has_numeric && is_numeric($data)) {
			return $data;
		}

		// TODO: Need to implement File size checking

		return strlen($data);
	}
}
