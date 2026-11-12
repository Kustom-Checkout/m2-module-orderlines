<?php
/**
 * Copyright © Kustom AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Test\Unit\Model\Items\Customerbalance;

use Klarna\Orderlines\Model\Container\DataHolder;
use Klarna\Orderlines\Model\Container\Parameter;
use Klarna\Orderlines\Model\Items\Customerbalance\Calculator;
use Klarna\Orderlines\Model\Items\Customerbalance\Processor;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Orderlines\Model\Items\Customerbalance\Processor
 */
class ProcessorTest extends TestCase
{
    /**
     * @var DataHolder
     */
    private DataHolder $dataHolder;

    public function testProcessSettingCustomerBalanceUnitPrice(): void
    {
        $value = 123;
        $calculator = $this->createStub(Calculator::class);
        $calculator->method('getUnitPrice')
            ->willReturn($value);
        $parameter = $this->createMock(Parameter::class);
        $parameter->expects(static::once())
            ->method('setCustomerBalanceUnitPrice')
            ->with($value);

        (new Processor($calculator))->process($this->dataHolder, $parameter);
    }

    public function testProcessSettingCustomerBalanceTaxRate(): void
    {
        $value = 123;
        $calculator = $this->createStub(Calculator::class);
        $calculator->method('getTaxRate')
            ->willReturn($value);
        $parameter = $this->createMock(Parameter::class);
        $parameter->expects(static::once())
            ->method('setCustomerBalanceTaxRate')
            ->with($value);

        (new Processor($calculator))->process($this->dataHolder, $parameter);
    }

    public function testProcessSettingCustomerBalanceTotalAmount(): void
    {
        $value = 123;
        $calculator = $this->createStub(Calculator::class);
        $calculator->method('getTotalAmount')
            ->willReturn($value);
        $parameter = $this->createMock(Parameter::class);
        $parameter->expects(static::once())
            ->method('setCustomerBalanceTotalAmount')
            ->with($value);

        (new Processor($calculator))->process($this->dataHolder, $parameter);
    }

    public function testProcessSettingCustomerBalanceTaxAmount(): void
    {
        $value = 123;
        $calculator = $this->createStub(Calculator::class);
        $calculator->method('getTaxAmount')
            ->willReturn($value);
        $parameter = $this->createMock(Parameter::class);
        $parameter->expects(static::once())
            ->method('setCustomerBalanceTaxAmount')
            ->with($value);

        (new Processor($calculator))->process($this->dataHolder, $parameter);
    }

    public function testProcessSettingCustomerBalanceTitle(): void
    {
        $value = 'my output';
        $calculator = $this->createStub(Calculator::class);
        $calculator->method('getTitle')
            ->willReturn($value);
        $parameter = $this->createMock(Parameter::class);
        $parameter->expects(static::once())
            ->method('setCustomerBalanceTitle')
            ->with($value);

        (new Processor($calculator))->process($this->dataHolder, $parameter);
    }

    public function testProcessSettingCustomerBalanceReference(): void
    {
        $value = 'my output';
        $calculator = $this->createStub(Calculator::class);
        $calculator->method('getReference')
            ->willReturn($value);
        $parameter = $this->createMock(Parameter::class);
        $parameter->expects(static::once())
            ->method('setCustomerBalanceReference')
            ->with($value);

        (new Processor($calculator))->process($this->dataHolder, $parameter);
    }

    protected function setUp(): void
    {
        $this->dataHolder = $this->createStub(DataHolder::class);
    }
}
