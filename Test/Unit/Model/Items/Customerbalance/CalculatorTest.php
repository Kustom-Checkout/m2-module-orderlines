<?php
/**
 * Copyright © Kustom AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Test\Unit\Model\Items\Customerbalance;

use Klarna\Base\Helper\DataConverter;
use Klarna\Orderlines\Model\Container\DataHolder;
use Klarna\Orderlines\Model\Items\Customerbalance\Calculator;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Orderlines\Model\Items\Customerbalance\Calculator
 */
class CalculatorTest extends TestCase
{
    /**
     * @var DataHolder
     */
    private DataHolder $dataHolder;

    public function testCalculateAmountIsZero(): void
    {
        $dataConverter = $this->createStub(DataConverter::class);
        $dataConverter->method('toApiFloat')
            ->willReturn(0);
        $calculator = new Calculator($dataConverter);
        $calculator->calculate($this->dataHolder);

        static::assertEquals(0, $calculator->getUnitPrice());
        static::assertEquals(0, $calculator->getTotalAmount());
        static::assertEquals(0, $calculator->getTaxRate());
        static::assertEquals(0, $calculator->getTaxAmount());
        static::assertEquals('Customer Balance', $calculator->getTitle());
        static::assertEquals('customerbalance', $calculator->getReference());
    }

    public function testCalculateAmountIsNotZero(): void
    {
        $dataConverter = $this->createStub(DataConverter::class);
        $dataConverter->method('toApiFloat')
            ->willReturn(-5);
        $calculator = new Calculator($dataConverter);
        $calculator->calculate($this->dataHolder);

        static::assertEquals(-5, $calculator->getUnitPrice());
        static::assertEquals(-5, $calculator->getTotalAmount());
        static::assertEquals(0, $calculator->getTaxRate());
        static::assertEquals(0, $calculator->getTaxAmount());
        static::assertEquals('Customer Balance', $calculator->getTitle());
        static::assertEquals('customerbalance', $calculator->getReference());
    }

    protected function setUp(): void
    {
        $this->dataHolder = $this->createStub(DataHolder::class);
    }
}
