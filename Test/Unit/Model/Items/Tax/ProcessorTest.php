<?php
/**
 * Copyright © Kustom AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Test\Unit\Model\Items\Tax;

use Klarna\Orderlines\Model\Container\DataHolder;
use Klarna\Orderlines\Model\Container\Parameter;
use Klarna\Orderlines\Model\Items\Tax\Calculator;
use Klarna\Orderlines\Model\Items\Tax\Processor;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Orderlines\Model\Items\Tax\Processor
 */
class ProcessorTest extends TestCase
{
    /**
     * @var DataHolder
     */
    private DataHolder $dataHolder;

    public function testProcessSettingTaxUnitPrice(): void
    {
        $value = 123;
        $calculator = $this->createStub(Calculator::class);
        $calculator->method('getUnitPrice')
            ->willReturn($value);
        $parameter = $this->createMock(Parameter::class);
        $parameter->expects(static::once())
            ->method('setTaxUnitPrice')
            ->with($value);

        (new Processor($calculator))->process($this->dataHolder, $parameter);
    }

    public function testProcessSettingTotalTaxAmount(): void
    {
        $value = 123;
        $calculator = $this->createStub(Calculator::class);
        $calculator->method('getTaxAmount')
            ->willReturn($value);
        $parameter = $this->createMock(Parameter::class);
        $parameter->expects(static::once())
            ->method('setTaxTotalAmount')
            ->with($value);

        (new Processor($calculator))->process($this->dataHolder, $parameter);
    }

    protected function setUp(): void
    {
        $this->dataHolder = $this->createStub(DataHolder::class);
    }
}
