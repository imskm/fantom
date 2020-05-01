<?php

namespace Fantom\Validation;

use Fantom\Session;
use Fantom\Database\Connector;
use Fantom\Validation\RulesTrait;
use Fantom\Validation\ErrorMessageBag;

/**
 * Validator Class
 *  Class contains function for validating user input
 */
class Validator
{
	/**
	 * @var string $validateField   stores field (one) to be validated
	 */
	protected $validateField;

	/**
	 * @var string $validateValue   stores value (one) of the field to be validated
	 */
	protected $validateValue;

	/**
	 * @var array $validatedFields   stores validated field as key and value as value
	 */
	public $validatedFields = [];

	/**
	 * @var array $rules   stores rules that will be performed on input field
	 */
	protected $rules = [];

	/**
	 * @var string $redirectUrl   stores URL for redirection if validation fails
	 */
	protected $redirectUrl;

	/**
	 * @var string $reqMethod   stores request method (GET | POST)
	 */
	protected $reqMethod;

	/**
	 * Bail out. If set to true then validation will stop as soon as it finds
	 * first field that doesn't passes current rule
	 * If set to false then it will continue to test all rules even if some
	 * rules doesn't pass.
	 * This property can be overridden by user in his child class
	 */
	protected $bail = true;

	/**
	 * @var array 				array of error messages of failed rules
	 * structure:
	 * 	[
	 *		'field_name1' => [
	 *				'rule1' => 'error message of rule1',
	 *				'rule2' => 'error message of rule2',
	 *			],
	 *		'field_name2' => [
	 *				'rule1' => 'error message of rule1',
	 *				'rule2' => 'error message of rule2',
	 *			],
	 * 	]
	 */
	public $errors = [];

	/**
	 * Custom error property name defined by user (it must be custom_messages)
	 */
	private $user_messages_property_name = 'custom_messages';

	/**
	 * HTML Message template defined by user
	 */
	private $user_message_template_name = 'message_template';

	/**
	 * Session storage name for storing validation errors persistently
	 */
	private static $session_storage = 'VAL_ERRORS_BAG';
	private static $session_storage_errors_name = 'messages';
	private static $session_storage_template_name = 'template';

	/**
	 * PHP Reflection object
	 */
	protected $reflection;

	public function __construct()
	{
		// PHP's Reflection mechanism, used to query info about $this object
		// Needed to check for user defined property existence check, such as
		// custom_messages and message_template
		$this->reflection = new \ReflectionObject($this);
	}

	use RulesTrait;

	public function validate($request, array $rules)
	{
		// 1. Setting request method
		$this->setRequestMethod($request);

		// 2. Itterating over fields to be validated
		// 2.1. Cheking if $rules is empty
		if(!$rules) {
			throw new \Exception("Empty rules is illegal. Please provide validation rules.");
		}

		// 2.2 Now itterating over fields
		foreach ($rules as $field => $rule_string) {

			// Sets the $validateField/Value property with current value of the field.
			$this->setCurrentValidation($field);

			//  Extract the rules and store it to $rules property
			$this->checkRules($rule_string);

			// Itterate over each rule and call the method
			foreach ($this->rules as $rule => $params) {

				// If rule has parameter then call the rule with parameter
				if($params) {

					$method 	= $rule;
					$argument 	= $this->rules[$rule];

					// calling the function with argument
					if(!$this->$method($this->validateField, $this->validateValue, $argument)) {

						// TODO
						if ($this->bail) break;
					}

				// else rule has no argument
				} else {

					$method 	= $rule;

					// Exceptional case checking for exceptional rules
					if($this->isRuleExceptional($rule)) {
						if(!$this->$method($this->validateField, $this->validateValue))
							if ($this->bail) break;
					}

					// calling method without argument
					if(!$this->$method($this->validateField, $this->validateValue)) {
						// Break out of loop and don't check the next rule
						// since first rule is not passed then we don't check
						// for next rule on the current input field

						// TODO: we can do something from users pov then break
						if ($this->bail) break;
					}
				}
			}

			// Storing the validated value to the validatedFields property
			$this->validatedFields[$this->validateField] = trim($this->validateValue);

			// Resetting the $rules property
			$this->rules = array();
		}

		$this->storeErorrs();
	}

	protected function checkRules($rules)
	{
		$rules = str_replace(" ", "", $rules);
		$rules_parts = explode("|", $rules);

		// $pattern = "/^([a-z_]+):([a-z0-9,]+)$/";
		$pattern = "/^([a-z_]+):(.+)$/";

		foreach ($rules_parts as $rule_part) {

			if(preg_match($pattern, $rule_part, $matches)) {
				$rule = $this->getRuleIfExist($matches[1]);

				// If rule has argument then store rule with argument
				// $matches[2] is argument of rule
				if( isset($matches[2]) ) {
					$this->rules[ $rule ] = $matches[2];

				} else {	// else just store rule with null argument
					$this->rules[ $rule ] = null;
				}

			} else {
				$rule = $this->getRuleIfExist($rule_part);
				$this->rules[ $rule ] = null;
			}
		}

		return true;
	}

	/**
	 * Checkin if rule exist or not
	 * TODO: suffix "_rule" in $rule then check the method exist
	 *       user must create rule suffixed with "_rule" word	
	 */
	protected function getRuleIfExist($rule)
	{
		if(!$this->isRuleAllowed($rule)) {
			// check if user's rule exist or not
			$custom_rule = $rule."_rule";
			if (!$this->userMethodExist($custom_rule)) {
				throw new \Exception("_rule " . $rule . " Not found.");
			}
			$rule = $custom_rule;
		}

		return $rule;
	}

	protected function getInputValue($field = "")
	{
		switch ($this->reqMethod) {
			case 'GET':
				if($field && isset($_GET[$field])) {
					return $_GET[$field];
				}

				return isset($_GET[$this->validateField])? $_GET[$this->validateField] : "";
				break;

			case 'POST':
			case 'PUT':
				if($field && isset($_POST[$field])) {
					// echo "$field\n $_POST[$field]\n";
					return $_POST[$field];
				}


				return isset($_POST[$this->validateField])? $_POST[$this->validateField] : "";
				break;

			default :
				throw new \Exception("$this->reqMethod not found.");
		}
	}

	protected function setRequestMethod($request)
	{
		$this->reqMethod = strtoupper($request);
	}

	protected function setCurrentValidation($field)
	{
		$this->validateField = $field;
		$this->validateValue = $this->getInputValue($field);
		return true;
	}

	protected function storeErorrs()
	{
		$bag[self::$session_storage_errors_name] = $this->errors;
		$bag[self::$session_storage_template_name] = $this->getMessageTemplate();
		Session::set(self::$session_storage, $bag);
	}

	protected function getMessageTemplate()
	{
		if (!$this->reflection->hasProperty($this->user_message_template_name)) {
			return "";
		}

		return $this->{$this->user_message_template_name};
	}

	protected function setError($field, $rule, $message)
	{
		// If user has defined custom_messages property then store user's
		// custom message
		if ($this->userPropertyExist($this->user_messages_property_name)) {
			$this->setCustomMessage($field, $rule, $message);
			return;
		}

		// Else store default message generated by Validator class
		$this->setDefaultMessage($field, $rule, $message);

	}

	protected function userMethodExist($method)
	{
		return $this->reflection->hasMethod($method);
	}

	protected function userPropertyExist($property)
	{
		return $this->reflection->hasProperty($property);
	}

	protected function setDefaultMessage($field, $rule, $message)
	{
		$this->errors[$field][$rule] = $message;
	}

	protected function setCustomMessage($field, $rule, $default_message = "")
	{
		// Check if user defined custom message doesn't exist for $rule rule
		// then drop back to default message
		if (!isset($this->{$this->user_messages_property_name}[$field][$rule])) {
			$this->setDefaultMessage($field, $rule, $default_message);
			return;
		}

		$this->errors[$field][$rule] = $this
										->{$this->user_messages_property_name}
										[$field][$rule];
	}

	public function hasError()
	{
		return $this->errors? true : false;
	}

	public function errors()
	{
		return $this->errors;
	}

	/**
	 * Method for retrieving error messages flushed in session
	 * this should be called for retrieving any errors occured
	 * in previous validation request
	 */
	public static function validationErrors()
	{
		$errors = self::getErrorsFromSession();
		$template = self::getMessageTemplateFromSession();

		Session::delete(self::$session_storage);

		return new ErrorMessageBag($errors, $template);
	}

	private static function getErrorsFromSession()
	{
		$bag = Session::get(self::$session_storage);
		if (!$bag) {
			return [];
		}

		return $bag[self::$session_storage_errors_name];
	}

	private static function getMessageTemplateFromSession()
	{
		$bag = Session::get(self::$session_storage);
		if (!$bag) {
			return "";
		}

		return $bag[self::$session_storage_template_name];
	}


	protected function fieldHasRule($field, $rules)
	{
		if (is_array($rules)) {
			foreach ($rules as $rule) {
				if (array_key_exists($rule, $this->rules)) {
					return true;
				}
			}

			return false;
		}

		return array_key_exists($rules, $this->rules);
	}
}
