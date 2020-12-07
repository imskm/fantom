<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Fantom\Tests\Auth\FakeForgotPasswordController;


class ForgotPasswordControllerTest extends TestCase
{
	public function testFakeForgotPasswordControllerObjectCanBeCreated()
	{
		$this->assertInstanceOf(FakeForgotPasswordController::class, new FakeForgotPasswordController());
	}

	/**
	 * This annotation solves the test error when redirect function is called from controller
	 * @runInSeparateProcess
	 */
	public function testSuccessPasswordResetLinkIsSendable()
	{
		$_SERVER['SERVER_NAME'] = "localhost"; // Hack to bypass path_for function crash
		$_POST['email'] = getenv("myemail_address");
		$controller = new FakeForgotPasswordController();
		$controller->sendPasswordResetLinkX();
	}
}
