<?php
/**
 * Copyright © Kustom AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Test\Unit\Model\Items\Item;

use Klarna\Orderlines\Model\Container\DataHolder;
use Klarna\Orderlines\Model\Container\Parameter;
use Klarna\Orderlines\Model\Items\Item\Handler;
use Klarna\Orderlines\Model\Items\Item\Extraction\Iterator;
use Klarna\Orderlines\Model\Items\Item\Validator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

/**
 * @coversDefaultClass \Klarna\Orderlines\Model\Items\Item\Handler
 */
#[AllowMockObjectsWithoutExpectations]
class HandlerTest extends TestCase
{
    /**
     * @var DataHolder
     */
    private DataHolder $dataHolder;
    /**
     * @var Validator|MockObject
     */
    private Validator $validator;
    /**
     * @var Iterator|MockObject
     */
    private Iterator $iterator;
    /**
     * @var Parameter
     */
    private Parameter $parameter;
    /**
     * @var Handler
     */
    private Handler $model;
    /**
     * @var Quote
     */
    private Quote $magentoQuote;
    /**
     * @var Order
     */
    private Order $magentoOrder;

    public function testCollectPrePurchaseNotCollectableImpliesNoCalculation(): void
    {
        $this->validator->method('isCollectable')->willReturn(false);

        $this->iterator->expects(static::never())
            ->method('getCalculatedItems');
        $this->model->collectPrePurchase($this->parameter, $this->dataHolder, $this->magentoQuote);
    }

    public function testCollectPrePurchaseCollectableImpliesCalculationMethodIsCalled(): void
    {
        $this->validator->method('isCollectable')->willReturn(true);

        $this->iterator->expects(static::once())
            ->method('getCalculatedItems')
            ->willReturn([]);
        $this->model->collectPrePurchase($this->parameter, $this->dataHolder, $this->magentoQuote);
    }

    public function testCollectPostPurchaseNotCollectableImpliesNoCalculation(): void
    {
        $this->validator->method('isCollectable')->willReturn(false);

        $this->iterator->expects(static::never())
            ->method('getCalculatedItems');
        $this->model->collectPostPurchase($this->parameter, $this->dataHolder, $this->magentoOrder);
    }

    public function testCollectPostPurchaseCollectableImpliesCalculationMethodIsCalled(): void
    {
        $this->validator->method('isCollectable')->willReturn(true);

        $this->iterator->expects(static::once())
            ->method('getCalculatedItems')
            ->willReturn([]);
        $this->model->collectPostPurchase($this->parameter, $this->dataHolder, $this->magentoOrder);
    }

    public function testFetchNotFetchableImpliesNoOrderLineItemIsAdded(): void
    {
        $this->validator->method('isFetchable')
            ->willReturn(false);
        $this->parameter->method('getItems')
            ->willReturn([['foo' => 'bar']]);
        $this->parameter->expects(static::never())
            ->method('addOrderLine');

        $this->model->fetch($this->parameter);
    }

    public function testFetchFetchableImpliesOrderLineItemIsAdded(): void
    {
        $this->validator->method('isFetchable')
            ->willReturn(true);
        $this->parameter->method('getItems')
            ->willReturn([['foo' => 'bar']]);
        $this->parameter->expects(static::once())
            ->method('addOrderLine');

        $this->model->fetch($this->parameter);
    }

    protected function setUp(): void
    {
        $this->validator = $this->createStub(Validator::class);
        $this->iterator = $this->createMock(Iterator::class);
        $this->model = new Handler($this->validator, $this->iterator);

        $this->dataHolder = $this->createStub(DataHolder::class);
        $this->parameter = $this->createMock(Parameter::class);
        $this->magentoQuote = $this->createStub(Quote::class);
        $this->magentoOrder = $this->createStub(Order::class);
    }
}
