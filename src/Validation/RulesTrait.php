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
		"alpha_num_space"	=> true,
		"confirmed"			=> true,
		"date"				=> true,
		"date_equals"		=> true,
		"depends"			=> true,
		"digits"			=> true,
		"email"				=> true,
		"exist"				=> true,
		"file"				=> true,
		"in"				=> true,
		"integer"			=> true,
		"max"				=> true,
		"min"				=> true,
		"numeric"			=> true,
		"optional"			=> true,
		"phone"				=> true,
		"required"			=> true,
		"required_file"		=> true,
		"size"				=> true,
		"unique"			=> true,
		"unique_xself"		=> true,
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

	/**
	 * Error message stored by file validation helper methods
	 * for file upload.
	 */
	protected $file_upload_error_msg = "";

	/**
	 * Allowed mime types
	 */
	protected $allowed_mimes = [
		'jpg' 	=> 'image/jpeg',
		'jpeg'	=> 'image/jpeg',
		'png'	=> 'image/png',
		'gif'	=> 'image/gif',
		'svg'	=> 'image/svg+xml',
		'bmp'	=> 'image/bmp',
		'webp'	=> 'image/webp',
		'image' => [
			'jpg' 	=> 'image/jpeg',
			'jpeg'	=> 'image/jpeg',
			'png'	=> 'image/png',
			'gif'	=> 'image/gif',
			'svg'	=> 'image/svg+xml',
			'bmp'	=> 'image/bmp',
			'webp'	=> 'image/webp',
		],
		'pdf'	=> 'application/pdf',
		'mp4'	=> 'video/mp4',
	];


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

	protected function alpha_num_space($field, $data)
	{
		$pattern = "/^[a-zA-Z0-9 ]+$/";
		
		if(!preg_match($pattern, $data)) {
			$this->setError($field, __FUNCTION__, "$field should contain alphabets, numbers and spaces only.");
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

	/**
	 * Depends checks for any field validated before field under validation
	 * has passed or not. In other word validation passes for this rule when
	 * the field(s), which field under validation depends on, passed validation.
	 * Usage:
	 * 	['first_name' => 'required', 'some_field' => 'depends:first_name']
	 *        depends validation only passes when first_name validation test
	 *        passed before.
	 * Note: When field in depends argument is not tested before field under
	 *       validation then depends rule will default to true.
	 */
	protected function depends($field, $data, $args)
	{
		$args = explode(",", $args);

		// Remove all the fields which are not validated yet.
		$args = array_filter($args, function($element) {
			return array_key_exists($element, $this->validatedFields);
		});

		// If fields in $args has failed the validation test before then
		// set the error and return false
		foreach ($args as $arg) {
			if (array_key_exists($arg, $this->errors)) {
				$this->setError($field, __FUNCTION__, "$arg is required to be valid.");
				return false;
			}
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
	 * Checks field value under validation is unique this check excludes
	 * a particular record in database.
	 * Useful when we try to update uniuqe field in database but want to
	 * exclude this check from a particular record.
	 *
	 * Usage: nique_xself:table,column," . $value
	 *        record having $value under column column and table table will
	 *        be exclued from unique checking.
	 */
    protected function unique_xself($field, $data, $args)
    {
        // $args[0] => Table, $args[1] => column, $args[2] => id
        $parts = explode(",", $args);
        if (count($parts) !== 3) {
            throw new \Exception("Invalid argument to ".__FUNCTION__." rule.");
        }

        $sql = sprintf("SELECT 1 FROM %s WHERE id != :id AND %s = :%s", $parts[0], $parts[1], $parts[1]);
        if($this->dbQuickCheck($sql, [
            "id" => (int) $parts[2],
            "$parts[1]" => $data
        ])) {
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

	/**
	 * Checks if given file name $field exists in $_FILES array
	 * 
	 * @return bool  true if $field exists in $_FILES array otherwise false
	 */
	protected function required_file($field, $data)
    {
        // Check if $_FILE array is empty
        if (count($_FILES) === 0) {
            $this->setError($field, __FUNCTION__, "$field can not be empty");
            return false;
        }
        
        foreach ($_FILES[$field] as $file) {
            if (count($file) == 0) {
                $this->setError($field, __FUNCTION__, "All field of $field are required");
                return false;
            }
        }
        
        return true;
    }

    protected function file($field, $data, $args)
    {
    	// $args[0] -> file type, $args[1] -> size
        $args = explode(",", $args);
        $ret = true;
        
        $files = diverse_files($_FILES[$field]);
        foreach ($files as $file) {
            // 1. Check the file is submitted
            //    or Check for $_FILES Corruption Attack | Multiple Files
            if ($ret) {
                $ret = $this->fileCheckUndefined($file);
            }
            
            // 2. Check file has submitted properly
            if ($ret) {
                $ret = $this->fileCheckError($file);
            }
            
            // 3. Check the file size
            if ($ret) {
                $ret = $this->fileCheckSize($file, (int) $args[1]);
            }

            // 4. Check type of the file
            if ($ret) {
                $ret = $this->fileCheckType($file, $args[0]);
            }

            // 5. If validation failes set the error and return false
            if (!$ret) {
                $this->setError($field, __FUNCTION__, $this->file_upload_error_msg);
                return false;
            }
        }

        // 5. Every check passed so return true
        return true;
    }

    private function fileCheckUndefined($file)
    {
        // Check Undefined | $_FILES Corruption Attack | Multiple Files
        if (!isset($file['error']) || is_array($file['error'])) {
            $this->file_upload_error_msg = "Product image is required";
            return false;
        }

        return true;
    }

    private function fileCheckError($file)
    {
        $ret = true;

        // Check Error code
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            
            case UPLOAD_ERR_NO_FILE:
                $this->file_upload_error_msg = 'No file sent';
                $ret = false;
                break;

            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $this->file_upload_error_msg = 'Exceeded filesize limit';
                $ret = false;
                break;

            default:
                $this->file_upload_error_msg = 'Unknown errors';
                $ret = false;
                break;
        }

        return $ret;
    }

    private function fileCheckSize($file, $size)
    {
        if ($file['size'] > $size) {
            $this->file_upload_error_msg = "Exceeded max filesize limit";
            return false;
        }

        return true;
    }

    /**
     * Check file type
     */
    private function fileCheckType($file, $type)
    {
    	// Check mime type given in argument is allowed in
    	// allowed mimes list.
    	if (!in_array($type, $this->allowed_mimes)) {
    		throw new \Exception("Unknown mime type '$type' given in argument");
    	}

    	$user_mime = $this->allowed_mimes[$type];
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        $ret = true;
        if (is_array($user_mime)) {
	        if (($ext = array_search($mime, $user_mime, true)) === false) {
	            $ret = false;
	        }
        } else if ($user_mime !== $mime) {
        	$ret = false;
        }

        if (!$ret) {
        	$this->file_upload_error_msg = 'Invalid file uploaded, allowed '. $type;
        }

        return $ret;
    }


}
