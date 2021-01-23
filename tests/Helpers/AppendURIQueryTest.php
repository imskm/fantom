<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class AppendURIQueryTest extends TestCase
{
	private $function = "get_query_append";

	public function test_function_exist()
	{
		$this->assertTrue(function_exists($this->function));
	}

	public function test_can_append_given_query_in_existing_query()
	{
		$_SERVER["QUERY_STRING"] = "post/2/show&q=keyword";
		$_GET = [
			"post/2/show" => "",
			"q" => "keyword",
		];
		$append_query = ["category" => 1];
		$expected = "q=keyword&category=1";

		$this->assertNotEmpty($res = ($this->function)($append_query));
		$this->assertSame($expected, $res);
	}

	public function test_can_correctly_append_given_query_with_array()
	{
		$_SERVER["QUERY_STRING"] = "post/2/show&q=keyword&name=ibtesham";
		$_GET = [
			"post/2/show" => "",
			"q" => "keyword",
			"name" => "ibtesham",
		];
		$append_query = [
			"category" => 1,
			"colors" => [
				"red", "green", "blue",
			],
		];
		$expected = "q=keyword&name=ibtesham&category=1&colors%5B%5D=red"
					. "&colors%5B%5D=green"
					. "&colors%5B%5D=blue";

		$this->assertNotEmpty($res = ($this->function)($append_query));
		$this->assertSame($expected, $res);
	}
}
