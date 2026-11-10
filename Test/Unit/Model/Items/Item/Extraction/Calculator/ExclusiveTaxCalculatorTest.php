<?php
/**
 * Copyright © Kustom AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Test\Unit\Model\Items\Item\Extraction;

use Klarna\Base\Helper\DataConverter;
use Klarna\Orderlines\Model\Items\Item\Extraction\Calculator\BaseResult;
use Klarna\Orderlines\Model\Items\Item\Extraction\Calculator\ExclusiveTaxCalculator;
use Klarna\Orderlines\Model\Items\Item\Extraction\Container;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Orderlines\Model\Items\Item\Extraction\Calculator\ExclusiveTaxCalculator
 */
class ExclusiveTaxCalculatorTest extends TestCase
{
    /**
     * @var ExclusiveTaxCalculator
     */
    private ExclusiveTaxCalculator $model;
    /**
     * @var DataConverter|MockObject
     */
    private DataConverter $dataConverter;
    /**
     * @var Container
     */
    private Container $container;

    public function testGetOrderLineItemCheckingUnitPrice(): void
    {
        $this->container->method('getRowTotal')
            ->willReturn((float) 50);
        $this->container->method('getDiscountAmount')
            ->willReturn((float) 10);
        $this->dataConverter->method('toApiFloat')
            ->willReturnCallback(fn($float) =>
                match ($float) {
                    (float) 50 => (float) 50,
                    (float) 40 => 40
                });

        $result = $this->model->getOrderLineItem($this->container);
        static::assertEquals(50, $result['unit_price']);
    }

    public function testGetOrderLineItemCheckingTotalAmount(): void
    {
        $this->container->method('getRowTotal')
            ->willReturn((float) 50);
        $this->container->method('getDiscountAmount')
            ->willReturn((float) 10);
        $this->dataConverter->method('toApiFloat')
            ->willReturnCallback(fn($float) =>
                match ($float) {
                    (float) 50 => (float) 50,
                    (float) 40 => 40
                });

        $result = $this->model->getOrderLineItem($this->container);
        static::assertEquals(40, $result['total_amount']);
    }

    protected function setUp(): void
    {
        $baseResult = $this->createStub(BaseResult::class);
        $baseResult->method('getFromContainer')
            ->willReturn(['quantity' => 1]);
        $this->dataConverter = $this->createStub(DataConverter::class);
        $this->model = new ExclusiveTaxCalculator($baseResult, $this->dataConverter);
        $this->container = $this->createStub(Container::class);
    }
}
