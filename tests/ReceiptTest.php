<?php


namespace TDD\Test;

require dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use PHPUnit\Framework\TestCase;
use TDD\Receipt;

class ReceiptTest extends TestCase
{
	/**
	 * @var Receipt
	 */
	private Receipt $Receipt;

	public function setUp(): void
	{
		$this->Formatter = $this
			->getMockBuilder('TDD\Formatter')
			->setMethods(['currencyAmount'])
			->getMock();
		$this
			->Formatter
			->expects($this->any())
			->method('currencyAmount')
			->with($this->anything())
			->will($this->returnArgument(0));
		$this->Receipt = new Receipt($this->Formatter);
	}

	public function tearDown(): void
	{
		unset($this->Receipt);
	}

	/**
	 * @dataProvider provideSubtotal
	 *
	 * @param $items
	 * @param $expected
	 */
	public function testSubtotal(array $items, int $expected): void
	{
		$coupon = null;
		$output = $this->Receipt->subtotal($items, $coupon);
		$this->assertEquals(
			$expected,
			$output,
			"When summing total should equal {$expected}"
		);
	}

	public function provideSubtotal(): array
	{
		return [
			['Expect total to be 16' => [1, 2, 5, 8], 16],
			['Expect total to be 14' => [-1, 2, 5, 8], 14],
			['Expect total to be 16' => [1, 2, 8], 11],
		];
	}

	public function testSubtotalAndCoupon(): void
	{
		$input = [0, 2, 5, 8];
		$coupon = 0.20;
		$output = $this->Receipt->subtotal($input, $coupon);
		$this->assertEquals(
			12,
			$output,
			'When summing total should equal 12'
		);
	}

	public function testSubtotalException(): void
	{
		$input = [0, 2, 5, 8];
		$coupon = 1.20;
		$this->expectException('BadMethodCallException');
		$this->Receipt->subtotal($input, $coupon);
	}

	public function testPostTaxTotal(): void
	{
		$items = [0, 2, 5, 8];
		$coupon = null;

		$Receipt = $this
			->getMockBuilder('TDD\Receipt')
			->setMethods(['tax', 'subtotal'])
			->setConstructorArgs([$this->Formatter])
			->getMock();
		$Receipt
			->expects($this->once())
			->method('subtotal')
			->with($items, $coupon)
			->will($this->returnValue(10.00));
		$Receipt
			->expects($this->once())
			->method('tax')
			->with(10.00)
			->will($this->returnValue(1.00));
		$result = $Receipt->postTaxTotal($items, $coupon);

		$this->assertEquals(11.00, $result);
	}

	public function testTax(): void
	{
		$inputAmount = 10.00;
		$this->Receipt->tax = 0.1;
		$output = $this->Receipt->tax($inputAmount);
		$this->assertEquals(
			1.00,
			$output,
			'The tax calculation should equal 1.00'
		);
	}
}
