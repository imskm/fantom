<?php

namespace Fantom\Tests\Auth;

use Fantom\Support\Auth\User;
use Fantom\Support\Auth\Interfaces\PasswordResetLinkEmailable;

/**
 * 
 */
class FakeUser extends User
{
	protected $table = 'users';
	protected $primary = 'id';
	
	public function sendPasswordResetLinkByEmail(array $data): bool
	{
		return true;
	}
}