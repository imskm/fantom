<?php

namespace Fantom\Tests\Auth;

use Fantom\Support\Auth\User;
use Fantom\Support\Auth\PasswordReset;

/**
 * 
 */
class FakePasswordReset extends PasswordReset
{
	protected $table = 'password_resets';
	protected $primary = 'id';

}