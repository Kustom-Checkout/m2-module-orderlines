<?php
/**
 * Copyright © Kustom AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Test\Unit\Model\Items\Item\Extraction;

use Klarna\Orderlines\Model\Container\DataHolder;
use Klarna\Orderlines\Model\Items\Item\Extraction\Container;
use Klarna\Orderlines\Model\Items\Item\Extraction\ContainerFactory;
use Klarna\Orderlines\Model\Items\Item\Extraction\Coordinator;
use Klarna\Orderlines\Model\Items\Item\Extraction\Iterator;
use Klarna\Orderlines\Model\Items\Item\Extraction\ItemValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\Store;

/**
 * @coversDefaultClass \Klarna\Orderlines\Model\Items\Item\Extraction\Iterator
 */
#[AllowMockObjectsWithoutExpectations]
class IteratorTest extends TestCase
{
    /**
     * @var Iterator
     */
    private Iterator $model;
    /**
     * @var Quote
     */
    private Quote $magentoQuote;
    /**
     * @var DataHolder
     */
    private DataHolder $dataHolder;
    /**
     * @var Item
     */
    private Item $magentoQuoteItem;
    /**
     * @var Container
     */
    private Container $container;
    /**
     * @var ItemValidator|MockObject
     */
    private ItemValidator $itemValidator;
    /**
     * @var Coordinator|MockObject
     */
    private Coordinator $coordinator;

    public function testGetCalculatedItemsNoItemsImpliesNoOrderLineItemCalculated(): void
    {
        $this->dataHolder->method('getItems')
            ->willReturn([]);

        $this->container->expects(static::never())
            ->method('setValues');
        $this->coordinator->expects(static::never())
            ->method('createOrderLineItem');
        static::assertEquals([], $this->model->getCalculatedItems($this->dataHolder, $this->magentoQuote));
    }

    public function testGetCalculatedItemsIsBundledProductImpliesNoOrderLineItemCalculated(): void
    {
        $this->dataHolder->method('getItems')
            ->willReturn([$this->magentoQuoteItem]);
        $this->itemValidator->method('isBundledProductWithDynamicPriceType')
            ->with($this->magentoQuoteItem)
            ->willReturn(true);

        $this->container->expects(static::never())
            ->method('setValues');
        $this->coordinator->expects(static::never())
            ->method('createOrderLineItem');
        static::assertEquals([], $this->model->getCalculatedItems($this->dataHolder, $this->magentoQuote));
    }

    public function testGetCalculatedItemsHasInvalidParentProductImpliesNoOrderLineItemCalculated(): void
    {
        $this->dataHolder->method('getItems')
            ->willReturn([$this->magentoQuoteItem]);
        $this->itemValidator->method('hasInvalidParentProduct')
            ->with($this->magentoQuoteItem)
            ->willReturn(true);

        $this->container->expects(static::never())
            ->method('setValues');
        $this->coordinator->expects(static::never())
            ->method('createOrderLineItem');
        static::assertEquals([], $this->model->getCalculatedItems($this->dataHolder, $this->magentoQuote));
    }

    public function testGetCalculatedItemsIsBundledProductAndInvalidParentProductImpliesNoOrderLineItemCalculated(): void
    {
        $this->dataHolder->method('getItems')
            ->willReturn([$this->magentoQuoteItem]);
        $this->itemValidator->method('isBundledProductWithDynamicPriceType')
            ->with($this->magentoQuoteItem)
            ->willReturn(true);
        $this->itemValidator->method('hasInvalidParentProduct')
            ->with($this->magentoQuoteItem)
            ->willReturn(true);

        $this->container->expects(static::never())
            ->method('setValues');
        $this->coordinator->expects(static::never())
            ->method('createOrderLineItem');
        static::assertEquals([], $this->model->getCalculatedItems($this->dataHolder, $this->magentoQuote));
    }

    public function testGetCalculatedItemsCalculatingTheOrderLineItem(): void
    {
        $expected = ['abc' => 'def'];
        $this->dataHolder->method('getItems')
            ->willReturn([$this->magentoQuoteItem]);

        $this->container->expects(static::once())
            ->method('setValues');
        $this->coordinator->expects(static::once())
            ->method('createOrderLineItem')
            ->willReturn($expected);
        static::assertEquals([$expected], $this->model->getCalculatedItems($this->dataHolder, $this->magentoQuote));
    }

    protected function setUp(): void
    {
        $this->container = $this->createMock(Container::class);
        $containerFactory = $this->createStub(ContainerFactory::class);
        $containerFactory->method('create')
            ->willReturn($this->container);

        $this->itemValidator = $this->createMock(ItemValidator::class);
        $this->coordinator = $this->createMock(Coordinator::class);
        $this->model = new Iterator($this->itemValidator, $containerFactory, $this->coordinator);

        $store = $this->createStub(Store::class);
        $this->dataHolder = $this->createMock(DataHolder::class);
        $this->dataHolder->method('getStore')
            ->willReturn($store);
        $this->magentoQuote = $this->createStub(Quote::class);
        $this->magentoQuoteItem = $this->createStub(Item::class);
    }
}
