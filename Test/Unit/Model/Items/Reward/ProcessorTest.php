<?php
/**
 * Copyright © Kustom AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Test\Unit\Model\Items\Reward;

use Klarna\Orderlines\Model\Container\DataHolder;
use Klarna\Orderlines\Model\Container\Parameter;
use Klarna\Orderlines\Model\Items\Reward\Calculator;
use Klarna\Orderlines\Model\Items\Reward\Processor;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Orderlines\Model\Items\Reward\Processor
 */
class ProcessorTest extends TestCase
{
    /**
     * @var DataHolder
     */
    private DataHolder $dataHolder;

    public function testProcessSettingRewardUnitPrice(): void
    {
        $value = 100;
        $calculator = $this->createStub(Calculator::class);
        $calculator->method('getUnitPrice')
            ->willReturn($value);
        $parameter = $this->createMock(Parameter::class);
        $parameter->expects(static::once())
            ->method('setRewardUnitPrice')
            ->with($value);

        (new Processor($calculator))->process($this->dataHolder, $parameter);
    }

    public function testProcessSettingRewardTaxRate(): void
    {
        $value = 100;
        $calculator = $this->createStub(Calculator::class);
        $calculator->method('getTaxRate')
            ->willReturn($value);
        $parameter = $this->createMock(Parameter::class);
        $parameter->expects(static::once())
            ->method('setRewardTaxRate')
            ->with($value);

        (new Processor($calculator))->process($this->dataHolder, $parameter);
    }

    public function testProcessSettingRewardTotalAmount(): void
    {
        $value = 100;
        $calculator = $this->createStub(Calculator::class);
        $calculator->method('getTotalAmount')
            ->willReturn($value);
        $parameter = $this->createMock(Parameter::class);
        $parameter->expects(static::once())
            ->method('setRewardTotalAmount')
            ->with($value);

        (new Processor($calculator))->process($this->dataHolder, $parameter);
    }

    public function testProcessSettingRewardTaxAmount(): void
    {
        $value = 100;
        $calculator = $this->createStub(Calculator::class);
        $calculator->method('getTaxAmount')
            ->willReturn($value);
        $parameter = $this->createMock(Parameter::class);
        $parameter->expects(static::once())
            ->method('setRewardTaxAmount')
            ->with($value);

        (new Processor($calculator))->process($this->dataHolder, $parameter);
    }

    public function testProcessSettingRewardTitle(): void
    {
        $value = 'abc';
        $calculator = $this->createStub(Calculator::class);
        $calculator->method('getTitle')
            ->willReturn($value);
        $parameter = $this->createMock(Parameter::class);
        $parameter->expects(static::once())
            ->method('setRewardTitle')
            ->with($value);

        (new Processor($calculator))->process($this->dataHolder, $parameter);
    }

    public function testProcessSettingRewardReference(): void
    {
        $value = 'abc';
        $calculator = $this->createStub(Calculator::class);
        $calculator->method('getReference')
            ->willReturn($value);
        $parameter = $this->createMock(Parameter::class);
        $parameter->expects(static::once())
            ->method('setRewardReference')
            ->with($value);

        (new Processor($calculator))->process($this->dataHolder, $parameter);
    }

    protected function setUp(): void
    {
        $this->dataHolder = $this->createStub(DataHolder::class);
    }
}
