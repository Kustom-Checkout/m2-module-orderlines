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
use Klarna\Orderlines\Model\ItemGenerator;
use Klarna\Orderlines\Model\Items\Tax\Handler;
use Klarna\Orderlines\Model\Items\Tax\Processor;
use Klarna\Orderlines\Model\Items\Tax\Validator;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Orderlines\Model\Items\Tax\Handler
 */
class HandlerTest extends TestCase
{
    /**
     * @var DataHolder
     */
    private DataHolder $dataHolder;
    /**
     * @var Quote
     */
    private Quote $quote;
    /**
     * @var Order
     */
    private Order $order;
    /**
     * @var Validator
     */
    private Validator $validator;

    public function testCollectPrePurchaseNotCollectableAndWillNotBeProcessed(): void
    {
        $this->validator = $this->createStub(Validator::class);
        $this->validator->method('isCollectable')
            ->willReturn(false);
        $processor = $this->createMock(Processor::class);
        $processor->expects(static::never())
            ->method('process');

        $handler = $this->buildHandler($processor);
        $handler->collectPrePurchase($this->createStub(Parameter::class), $this->dataHolder, $this->quote);
    }

    public function testCollectPrePurchaseCollectableAndWillBeProcessed(): void
    {
        $this->validator = $this->createStub(Validator::class);
        $this->validator->method('isCollectable')
            ->willReturn(true);
        $processor = $this->createMock(Processor::class);
        $processor->expects(static::once())
            ->method('process');

        $handler = $this->buildHandler($processor);
        $handler->collectPrePurchase($this->createStub(Parameter::class), $this->dataHolder, $this->quote);
    }

    public function testCollectPostPurchaseNotCollectableAndWillNotBeProcessed(): void
    {
        $this->validator = $this->createStub(Validator::class);
        $this->validator->method('isCollectable')
            ->willReturn(false);
        $processor = $this->createMock(Processor::class);
        $processor->expects(static::never())
            ->method('process');

        $handler = $this->buildHandler($processor);
        $handler->collectPostPurchase($this->createStub(Parameter::class), $this->dataHolder, $this->order);
    }

    public function testCollectPostPurchaseCollectableAndWillBeProcessed(): void
    {
        $this->validator = $this->createStub(Validator::class);
        $this->validator->method('isCollectable')
            ->willReturn(true);
        $processor = $this->createMock(Processor::class);
        $processor->expects(static::once())
            ->method('process');

        $handler = $this->buildHandler($processor);
        $handler->collectPostPurchase($this->createStub(Parameter::class), $this->dataHolder, $this->order);
    }

    public function testFetchNotFetchableAndNoOrderLineItemWillBeAdded(): void
    {
        $this->validator = $this->createStub(Validator::class);
        $this->validator->method('isFetchable')
            ->willReturn(false);
        $parameter = $this->createMock(Parameter::class);
        $parameter->expects(static::never())
            ->method('addOrderLine');

        $this->buildHandler($this->createStub(Processor::class))->fetch($parameter);
    }

    public function testFetchFetchableAndOrderLineItemWillBeAdded(): void
    {
        $this->validator = $this->createStub(Validator::class);
        $this->validator->method('isFetchable')
            ->willReturn(true);
        $parameter = $this->createMock(Parameter::class);
        $parameter->expects(static::once())
            ->method('addOrderLine');

        $this->buildHandler($this->createStub(Processor::class))->fetch($parameter);
    }

    protected function setUp(): void
    {
        $this->validator = $this->createStub(Validator::class);
        $this->dataHolder = $this->createStub(DataHolder::class);
        $this->quote = $this->createStub(Quote::class);
        $this->order = $this->createStub(Order::class);
    }

    private function buildHandler(Processor $processor): Handler
    {
        return new Handler(
            $this->createStub(ItemGenerator::class),
            $this->validator,
            $processor
        );
    }
}
