# Validator class API

## User defined custom messages
If user wants to show his custom error message instead of default, when a rule fails then he should define protected `custom_messages` array property like:
```php
protected $custom_messages = [
	'field1' => [
		'rule1' => 'This is custom message for rule1 of field filed1',
		'rule2' => 'This is custom message for rule2 of field filed1',
	],

	'field2' => [
		'rule1' => 'This is custom message for rule1 of field filed2',
		'rule2' => 'This is custom message for rule2 of field filed2',
	],
];

# Example
protected $custom_messages = [
	'first_name' => [
		'required' => 'You must provide first name',
		'alpha_space' => 'Your name must be of alphabet and space nothing else.',
	],

	'password' => [
		'required' => 'You must provide password because it is required',
		'min' => 'Your password must be atleast 6 characters in length',
		'max' => 'Your password must be at max 16 characters in length',
	],
];

```

## User defined custom rules
User must define a method in his extended Validator class in this way:
Let say user want his custom rule for password validation, there for the method name msut be suffixed with "\_rule" like this:
```php
protected function password_rule($field, $data)
{
	//

	if ($validation_fails) {
		// __FUNCTION__ is rule name
		$this->setError($field, __FUNCTION__, $message = "this is the error message");
		return false;
	}

	return true;
}
```
and user must set error if validation fails and return false, returns true on validation passes.
Naming convention of the rule must be snake case for example, if new rule is custom password validation then it should be named as `custom_password_rule`.

## Rules
	- alpha
	- alpha_dash
	- alpha_num
	- alpha_space
	- confirmed
	- digits
	- email
	- exist
	- in
	- integer
	- max
	- min
	- numeric
	- optional
	- phone
	- required
	- size
	- unique

## size rule
NOTE: size rule will always fail if the data is number. This rule is not appropriate for number.
Warning: Do not use it for checking number size.

## Message template format
If user want to use powerful built in `emmitOrDefault()` method which will take field name for which user want to show error message with html template then
User should set the message template first by extending the Validator class and defining protected `message_template` property in his subclass as:
```php
protected $message_template = '<p class="form-error">%s</p>';
```
Note the `%s` is important. in place of `%s` error message will be injected

## Error Messgae Bag API
An instance of ErrorMessaageBag will always be available to every view, and every view can access the error message using `$this->errors` instance.

* Check if any error exists
```php
$this->errors->hasError();
```

* Check if a specific error exists
```php
$this->errors->hasError($error_key);
```

* Get all the error keys
This is useful when you validated a form and want to know all the form fields which has error. Using this errors you can show error message below every corresponding field.
```php
$this->errors->errorFields();
```

* Get the rule of specific field which has error
This is useful when you want to know the first rule of a specific field (form field) that has failed.
```php
$this->errors->errorIn('first_name')->errorRule();
```

* Get all the rules of specific field which has error
This is useful when you want to know the all the rules of a specific field (form field) that has failed.
```php
$this->errors->errorIn('first_name')->errorRules();
```

* Get all the errors as flat array (recomended)
This is very useful and it give you easy access and error printing, since errors are stored internally as multidimensional array.
```php
$this->errors->all();
```

* Get error message of a specific field
```php
$this->error->error('first_name');
```

* Count number of error occured
```php
count($this->errors);
```
