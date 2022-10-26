<?php

namespace Fantom\Validation;

use Countable;

/**
 * Validation Error Message class
 * Provides easy way to access error messages
 */
class ErrorMessageBag implements Countable
{
	protected $errors;
	protected $message_template;

	/**
	 * Stores current field name given by user to get rule
	 * that has error in. this will used by errorRule method
	 * By default it is set to _unknown_field_ since this field
	 * does not exist in errors property
	 */
	protected $current_key_for_error_field = "_unknown_field_";
	
	public function __construct(array $errors, $message_template)
	{
		$this->errors = $errors;
		$this->message_template = $message_template;
	}

	public function hasError($key = "")
	{
		// Check if key exist
		if ($key && !$this->isExists($key)) {
			return false;
		}

		// If key is given and error for given key exist then return true
		if ($key && $this->isExists($key)) {
			return true;
		}

		// No key is given so check any error exist
		return $this->errors? true : false;
	}

	public function isExists($key)
	{
		return array_key_exists($key, $this->errors);
	}

	public function errorFields()
	{
		return array_keys($this->errors);
	}

	public function errorIn($key)
	{
		$this->current_key_for_error_field = $key;

		return $this;
	}

	/**
	 * Returns first rule that has error in the field
	 * current_key_for_error_field
	 *
	 * @return string 	rule name if there is an error else empty string
	 */
	public function errorRule()
	{
		return $this->isExists($this->current_key_for_error_field)?
				array_keys($this->errors[$this->current_key_for_error_field])[0] :
				"";
	}

	/**
	 * Returns all rules that has error in the field
	 * current_key_for_error_field
	 *
	 * @return array 	rule name if there is errors else empty array
	 */
	public function errorRules()
	{
		return $this->isExists($this->current_key_for_error_field)?
				array_keys($this->errors[$this->current_key_for_error_field]) :
				[];
	}

	/**
	 * Builds flat array of errors, since errors property is multi dimensional
	 * [ 'field1' => "first error message, second error message if any", ... ]
	 *
	 * @return array  flat array of errors
	 */
	public function all()
	{
		$result = array();
		foreach ($this->errors as $field => $errors) {
			$result[$field] = $this->flattenArray($errors);
		}

		return $result;
	}

	public function error($key)
	{
		return $this->isExists($key)? 
				$this->flattenArray($this->errors[$key]) : "";
	}

	private function flattenArray(array $errors)
	{
		return implode(", ", $errors);
	}

	public function emmitOrDefault($key, $message_template = "")
	{
		if (!$this->isExists($key)) {
			return "";
		}

		// If $message_template is given then use this given template
		if ($message_template) {
			return sprintf($message_template, $this->error($key));
		}

		// If default template is set then use default template 
		if ($this->message_template) {
			return sprintf($this->message_template, $this->error($key));
		}

		// There is no template set by user, therefore return plain error message
		return $this->error($key);
	}

	public function count(): int
	{
		return count($this->errors);
	}
}
