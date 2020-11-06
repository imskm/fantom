<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Fantom\Job\Job;

final class JobTest extends TestCase
{
	public function testCanCreateJobObject()
	{
		$this->assertInstanceOf(Job::Class, new Job());
	}
}

