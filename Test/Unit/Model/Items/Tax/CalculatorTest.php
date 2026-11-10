<?php
/**
 * Copyright © Kustom AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Test\Unit\Model\Items\Tax;

use Klarna\Base\Helper\DataConverter;
use Klarna\Orderlines\Model\Container\DataHolder;
use Klarna\Orderlines\Model\Fpt\Validator;
use Klarna\Orderlines\Model\Items\Tax\Calculator;
use Magento\Store\Model\Store;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Orderlines\Model\Items\Tax\Calculator
 */
class CalculatorTest extends TestCase
{
    /**
     * @var DataHolder
     */
    private DataHolder $dataHolder;

    public function testCalculateNoFptUsed(): void
    {
        $this->dataHolder->method('getTotalTax')
            ->willReturn(0);
        $validator = $this->createStub(Validator::class);
        $validator->method('isFptUsable')
            ->willReturn(false);
        $dataConverter = $this->createMock(DataConverter::class);
        $dataConverter->method('toApiFloat')
            ->with(0)
            ->willReturn(0);
        $calculator = new Calculator($dataConverter, $validator);

        $calculator->calculate($this->dataHolder);

        static::assertEquals(0, $calculator->getUnitPrice());
        static::assertEquals(0, $calculator->getTotalAmount());
        static::assertEquals(0, $calculator->getTaxRate());
        static::assertEquals(0, $calculator->getTaxAmount());
        static::assertEquals('', $calculator->getTitle());
        static::assertEquals('', $calculator->getReference());
    }

    public function testCalculateFptUsed(): void
    {
        $this->dataHolder->method('getTotalTax')
            ->willReturn(0);
        $this->dataHolder->method('getFptTax')
            ->willReturn(['tax' => 2]);
        $validator = $this->createStub(Validator::class);
        $validator->method('isFptUsable')
            ->willReturn(true);
        $dataConverter = $this->createMock(DataConverter::class);
        $dataConverter->method('toApiFloat')
            ->with(2)
            ->willReturn(5);
        $calculator = new Calculator($dataConverter, $validator);

        $calculator->calculate($this->dataHolder);

        static::assertEquals(5, $calculator->getUnitPrice());
        static::assertEquals(0, $calculator->getTotalAmount());
        static::assertEquals(0, $calculator->getTaxRate());
        static::assertEquals(5, $calculator->getTaxAmount());
        static::assertEquals('', $calculator->getTitle());
        static::assertEquals('', $calculator->getReference());
    }

    protected function setUp(): void
    {
        $this->dataHolder = $this->createStub(DataHolder::class);
        $this->dataHolder->method('getStore')
            ->willReturn($this->createStub(Store::class));
    }
}
