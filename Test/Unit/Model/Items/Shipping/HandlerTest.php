<?php
/**
 * Copyright © Kustom AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Test\Unit\Model\Items\Shipping;

use Klarna\Orderlines\Model\Container\DataHolder;
use Klarna\Orderlines\Model\Container\Parameter;
use Klarna\Orderlines\Model\ItemGenerator;
use Klarna\Orderlines\Model\Items\Shipping\Handler;
use Klarna\Orderlines\Model\Items\Shipping\Processor;
use Klarna\Orderlines\Model\Items\Shipping\Validator;
use Magento\Sales\Model\Order;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class HandlerTest extends TestCase
{
    /**
     * @var Handler
     */
    private Handler $handler;
    /**
     * @var Validator
     */
    private Validator $validator;
    /**
     * @var Processor
     */
    private Processor $processor;
    /**
     * @var DataHolder
     */
    private DataHolder $dataHolder;
    /**
     * @var Parameter
     */
    private Parameter $parameter;
    /**
     * @var Quote
     */
    private Quote $quote;
    /**
     * @var Order
     */
    private Order $order;

    public function testCollectPrePurchaseNotCollectableImpliesNotProcessingData(): void
    {
        $this->validator->method('isCollectableForPrePurchase')
            ->willReturn(false);
        $this->processor->expects($this->never())
            ->method('processPrePurchase');

        $this->handler->collectPrePurchase($this->parameter, $this->dataHolder, $this->quote);
    }

    public function testCollectPrePurchaseCollectableImpliesProcessingData(): void
    {
        $this->validator->method('isCollectableForPrePurchase')
            ->willReturn(true);
        $this->processor->expects($this->once())
            ->method('processPrePurchase');

        $this->handler->collectPrePurchase($this->parameter, $this->dataHolder, $this->quote);
    }

    public function testCollectPostPurchaseNotCollectableImpliesNotProcessingData(): void
    {
        $this->validator->method('isCollectableForPostPurchase')
            ->willReturn(false);
        $this->processor->expects($this->never())
            ->method('processPostPurchase');

        $this->handler->collectPostPurchase($this->parameter, $this->dataHolder, $this->order);
    }

    public function testCollectPostPurchaseCollectableImpliesProcessingData(): void
    {
        $this->validator->method('isCollectableForPostPurchase')
            ->willReturn(true);
        $this->processor->expects($this->once())
            ->method('processPostPurchase');

        $this->handler->collectPostPurchase($this->parameter, $this->dataHolder, $this->order);
    }

    public function testFetchNotFetchableImpliesNotProcessingData(): void
    {
        $this->validator->method('isFetchable')
            ->willReturn(false);
        $this->parameter->expects($this->never())
            ->method('addOrderLine');

        $this->handler->fetch($this->parameter);
    }

    public function testFetchFetchableImpliesProcessingData(): void
    {
        $this->validator->method('isFetchable')
            ->willReturn(true);
        $this->parameter->expects($this->once())
            ->method('addOrderLine');

        $this->handler->fetch($this->parameter);
    }

    protected function setUp(): void
    {
        $this->validator = $this->createStub(Validator::class);
        $this->processor = $this->createMock(Processor::class);
        $this->handler = new Handler($this->createStub(ItemGenerator::class), $this->validator, $this->processor);

        $this->dataHolder = $this->createStub(DataHolder::class);
        $this->parameter = $this->createMock(Parameter::class);

        $this->quote = $this->createStub(Quote::class);
        $this->order = $this->createStub(Order::class);
    }
}
