<?php
/**
 * Copyright © Kustom AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Test\Unit\Model\Items\Giftcard;

use Klarna\Orderlines\Model\Container\DataHolder;
use Klarna\Orderlines\Model\Items\Giftcard\Calculator;
use Klarna\Base\Helper\DataConverter;
use Magento\Framework\DataObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Orderlines\Model\Items\Giftcard\Calculator
 */
class CalculatorTest extends TestCase
{
    /**
     * @var Calculator
     */
    private Calculator $calculator;
    /**
     * @var DataHolder
     */
    private DataHolder $dataHolder;
    /**
     * @var DataConverter&\PHPUnit\Framework\MockObject\Stub
     */
    private $dataConverter;

    public function testCalculateAmountIsZero(): void
    {
        $this->dataConverter->method('toApiFloat')
            ->willReturn(0);
        $this->calculator->calculate($this->dataHolder);

        static::assertEquals(0, $this->calculator->getUnitPrice());
        static::assertEquals(0, $this->calculator->getTotalAmount());
        static::assertEquals(0, $this->calculator->getTaxRate());
        static::assertEquals(0, $this->calculator->getTaxAmount());
        static::assertEquals('my_title', $this->calculator->getTitle());
        static::assertEquals('my_code', $this->calculator->getReference());
    }

    public function testCalculateAmountIsNotZero(): void
    {
        $this->dataConverter->method('toApiFloat')
            ->willReturn(5);
        $this->calculator->calculate($this->dataHolder);

        static::assertEquals(-5, $this->calculator->getUnitPrice());
        static::assertEquals(-5, $this->calculator->getTotalAmount());
        static::assertEquals(0, $this->calculator->getTaxRate());
        static::assertEquals(0, $this->calculator->getTaxAmount());
        static::assertEquals('my_title', $this->calculator->getTitle());
        static::assertEquals('my_code', $this->calculator->getReference());
    }

    protected function setUp(): void
    {
        $this->dataConverter = $this->createStub(DataConverter::class);
        $this->calculator = new Calculator($this->dataConverter);

        $total = new DataObject(['title' => __('my_title'), 'code' => 'my_code']);
        $this->dataHolder = new DataHolder();
        $this->dataHolder->setTotals(['giftcardaccount' => $total]);
    }
}
