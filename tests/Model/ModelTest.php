<?php

declare(strict_types=1);

use Fantom\Router;
use App\Models\User;
use PHPUnit\Framework\TestCase;

/**
 * --------------------------------------------
 *  Tests
 * --------------------------------------------
 *  1. Model object is created properly without any error
 *  2. Test the find method; find method returns existing record as User Model
 *  3. Test the find method; find method returns null when record not found
 *  4. Test the where method; where returns instance of User Model
 *  5. Test the where method; where with get returns array of User Model
 *  6. Test the all method; all returns array of User Model
 *  7. Test the where with orWhere; returns array of User Model
 *  8. Test the where with andWhere; returns array of User Model
 *  9. Test the first method; returns first result as User Model if record found
 * 10. Test the first method; returns null if record not found
 * 11. Test the last method; returns first result User Model if record found
 * 12. Test the last method; returns null if record not found
 * 13. Test where method; returns empty array when non existing match given
 * 14. Test where with orWhere method; returns empty array when record not found
 * 15. Test where with andWhere method; returns empty array when record not found
 * 16. Test save method; insert new record in db.
 * 17. Test delete method; delete record from db.
 * 18. Test save method; can update record
 * 19. Test save method; can update record when called on where return
 * 20. Test save method; can update record when called on where,orWhere return
 * 21. Test save method; can update record when called on where,andWhere return
 * 22. Test save method; can update record when called on all return
 *
 */

/**
 * ModelTest class
 */
final class ModelTest extends TestCase
{
	public function testModelObjectCanBeCreated()
	{
		$this->assertInstanceOf(User::class, new User());
	}

	public function testFindMethodCanFindExistingRecord()
	{
		$this->assertInstanceOf(User::class, User::find(2));
	}

	public function testFindMethodReturnsNullWhenRecordNotFound()
	{
		$this->assertEquals(null, User::find(20000));
	}

	public function testWhereMethodRetunsInstanceOfUserModelWhenRecordFound()
	{
		$this->assertInstanceOf(User::class, User::where('id', 2));
	}

	public function testWhereMethodRetunsInstanceOfUserModelWhenRecordNotFound()
	{
		$this->assertInstanceOf(User::class, User::where('id', 20000));
	}

	public function testWhereGetMethodRetunsArrayOfUserModelWhenRecordFound()
	{
		$this->assertContainsOnlyInstancesOf(
			User::class,
			User::where('password', '12345678')->get()
		);
	}

	public function testWhereGetMethodRetunsEmptyArrayWhenRecordNotFound()
	{
		$this->assertEmpty(User::where('id', 20000)->get());
	}

	public function testAllMethodRetunsInstanceOfUserModel()
	{
		$this->assertInstanceOf(User::class, User::all());
	}

	public function testAllGetMethodRetunsArrayOfUserModel()
	{
		$this->assertContainsOnlyInstancesOf(
			User::class,
			User::all()->get()
		);
	}

	public function testOrWhereMethodRetunsInstanceOfUserModel()
	{
		$this->assertInstanceOf(
			User::class,
			User::where('id', 2)->orWhere('id', 3)
		);
	}

	public function testOrWhereMethodRetunsArrayOfUserModel()
	{
		$this->assertContainsOnlyInstancesOf(
			User::class,
			User::where('id', 2)->orWhere('id', 3)->get()
		);
	}

	public function testOrWhereMethodRetunsEmptyArrayWhenRecordNotFound()
	{
		$this->assertEmpty(
			User::where('id', 2000)->orWhere('id', 3000)->get()
		);
	}

	public function testAndWhereMethodRetunsInstanceOfUserModel()
	{
		$this->assertInstanceOf(
			User::class,
			User::where('id', 2)->andWhere('id', 3)
		);
	}

	public function testAndWhereMethodRetunsArrayOfUserModel()
	{
		$this->assertContainsOnlyInstancesOf(
			User::class,
			User::where('id', 2)->andWhere('id', 3)->get()
		);
	}

	public function testAndWhereMethodRetunsArrayOfSizeOne()
	{
		$this->assertCount(
			1,
			User::where('id', 8)->andWhere('email', 'ibtesham@gmail.com')->get()
		);
	}

	public function testAndWhereMethodRetunsEmptyArrayWhenRecordNotFound()
	{
		$this->assertEmpty(
			User::where('id', 2000)->andWhere('id', 3000)->get()
		);
	}

	public function testFirstMethodReturnsUserObjectWhenRecordFound()
	{
		$this->assertInstanceOf(
			User::class,
			User::where('password', '12345678')->first()
		);
	}

	public function testFirstMethodReturnsNullWhenRecordNotFound()
	{
		$this->assertEquals(null, User::where('id', 20000)->first());
	}

	public function testLastMethodReturnsUserObjectWhenRecordFound()
	{
		$this->assertInstanceOf(
			User::class,
			User::where('password', '12345678')->last()
		);
	}

	public function testLastMethodReturnsNullWhenRecordNotFound()
	{
		$this->assertEquals(
			null,
			User::where('id', 20000)->last()
		);
	}

	public function testSaveMethodCanCreateNewRecord()
	{
		$user = new User();
		$user->name 		= 'PHPUnit';
		$user->email 		= 'phpunit@test.com';
		$user->password 	= '12345678';

		$this->assertTrue($user->save());
	}

	public function testDeleteMethodCanDeleteRecordCreatedByPreviousTest()
	{
		$user = User::where('email', 'phpunit@test.com')->first();

		$this->assertTrue($user->delete());
	}

	public function testSaveMethodCanUpdateRecord()
	{
		$user = User::find(2)->first();
		$user->name 		= 'Updated';

		$this->assertTrue($user->save());
	}

	public function testSaveMethodCanUpdateRecordAfterWhereReturns()
	{
		$user = User::where('email', 'sadaf@gmail.com')->first();
		$user->name 		= 'Sadaf Anjum';

		$this->assertTrue($user->save());
	}

	public function testSaveMethodCanUpdateRecordAfterWhereOrWhereReturns()
	{
		$user = User::where('email', 'sadaf@gmail.com')
			->orWhere('id', 2)->first();
		$user->name 		= 'Sadaf Anjum WhereOrWhere';

		$this->assertTrue($user->save());
	}

	public function testSaveMethodCanUpdateRecordAfterWhereAndWhereReturns()
	{
		$user = User::where('email', 'wasim@gmail.com')
			->andWhere('id', 6)->first();
		$user->name 		= 'Wasim WhereAndWhere';

		$this->assertTrue($user->save());
	}

	public function testSaveMethodCanUpdateRecordAfterAllReturns()
	{
		$users = User::all()->get();
		foreach ($users as $user) {
			$user->name = 'Updated';

			$this->assertTrue($user->save());
			$user = null;
		}
	}

	public function testSaveMethodCanUpdateWithOriginalNameOfUsersDUMMY()
	{
		$names = [
			'Sadaf Anjum', 'Wasim', 'Sagar', 'Ibtesham', 'Muktar'
		];
		$users = User::all()->get();
		foreach ($users as $i => $user) {
			$user->name = $names[$i];

			$this->assertTrue($user->save());
			$user = null;
		}
	}

}
