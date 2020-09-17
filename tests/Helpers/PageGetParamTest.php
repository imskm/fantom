<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * PageGetParamTest class
 */
final class PageGetParamTest extends TestCase
{
	public function testGetPageHelperFunctionCanReturnPageNumber()
	{
		$page_numbers = [
			"1", "0", "99999999999999", "", "-10",
		];
		$expected_numbers = [
			1, 1, 99999999999999, 1, 10,
		];

		foreach ($page_numbers as $i => $pn) {
			$_GET['page'] = $pn;
			$this->assertEquals($expected_numbers[$i], get_page());
		}
	}

	public function testGetNextPageHelperFunctionCanReturnPageNumber()
	{
		$page_numbers = [
			"1", "0", "99999999999999", "", "-10",
		];
		$expected_numbers = [
			2, 2, 100, 2, 11, // 11 becuase get_page returns abs("-10")
		];

		foreach ($page_numbers as $i => $pn) {
			$_GET['page'] = $pn;
			$this->assertEquals($expected_numbers[$i], get_page_next());
		}
	}

	public function testGetPrevPageHelperFunctionCanReturnPageNumber()
	{
		$page_numbers = [
			"1", "0", "99999999999999", "", "-10",
		];
		$expected_numbers = [
			1, 1, 99999999999998, 1, 9, // 9 becuase get_page returns abs("-10")
		];

		foreach ($page_numbers as $i => $pn) {
			$_GET['page'] = $pn;
			$this->assertEquals($expected_numbers[$i], get_page_prev());
		}
	}
}
