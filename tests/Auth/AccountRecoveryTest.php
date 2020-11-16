<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Fantom\Tests\Auth\FakeUser as Authenticatable;

class AccountRecoveryTest extends TestCase
{
	public function testAuthenticatableObjectCanBeCreated()
	{
		$this->assertInstanceOf(Authenticatable::class, new Authenticatable());
	}

}