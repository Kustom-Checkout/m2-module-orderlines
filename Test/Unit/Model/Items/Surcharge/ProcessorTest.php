<?php
/**
 * Copyright © Kustom AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Test\Unit\Model\Items\Surcharge;

use Klarna\Orderlines\Model\Container\DataHolder;
use Klarna\Orderlines\Model\Container\Parameter;
use Klarna\Orderlines\Model\Items\Surcharge\Calculator;
use Klarna\Orderlines\Model\Items\Surcharge\Processor;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Orderlines\Model\Items\Surcharge\Processor
 */
class ProcessorTest extends TestCase
{
    /**
     * @var DataHolder
     */
    private DataHolder $dataHolder;

    public function testProcessSettingSurchargeUnitPrice(): void
    {
        $value = 100;
        $calculator = $this->createStub(Calculator::class);
        $calculator->method('getUnitPrice')
            ->willReturn($value);
        $parameter = $this->createMock(Parameter::class);
        $parameter->expects(static::once())
            ->method('setSurchargeUnitPrice')
            ->with($value);

        (new Processor($calculator))->process($this->dataHolder, $parameter);
    }

    public function testProcessSettingTotalAmount(): void
    {
        $value = 100;
        $calculator = $this->createStub(Calculator::class);
        $calculator->method('getTotalAmount')
            ->willReturn($value);
        $parameter = $this->createMock(Parameter::class);
        $parameter->expects(static::once())
            ->method('setSurchargeTotalAmount')
            ->with($value);

        (new Processor($calculator))->process($this->dataHolder, $parameter);
    }

    public function testProcessSettingReference(): void
    {
        $value = 'abc';
        $calculator = $this->createStub(Calculator::class);
        $calculator->method('getReference')
            ->willReturn($value);
        $parameter = $this->createMock(Parameter::class);
        $parameter->expects(static::once())
            ->method('setSurchargeReference')
            ->with($value);

        (new Processor($calculator))->process($this->dataHolder, $parameter);
    }

    public function testProcessSettingName(): void
    {
        $value = 'abc';
        $calculator = $this->createStub(Calculator::class);
        $calculator->method('getTitle')
            ->willReturn($value);
        $parameter = $this->createMock(Parameter::class);
        $parameter->expects(static::once())
            ->method('setSurchargeName')
            ->with($value);

        (new Processor($calculator))->process($this->dataHolder, $parameter);
    }

    protected function setUp(): void
    {
        $this->dataHolder = $this->createStub(DataHolder::class);
    }
}
