<?php
/**
 * Copyright © Kustom AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Test\Unit\Model\Items\Item\Extraction;

use Klarna\Base\Model\Quote\Address\Country;
use Klarna\Orderlines\Model\Container\DataHolder;
use Klarna\Orderlines\Model\Items\Item\Extraction\Calculator\ExclusiveTaxCalculator;
use Klarna\Orderlines\Model\Items\Item\Extraction\Calculator\InclusiveTaxCalculator;
use Klarna\Orderlines\Model\Items\Item\Extraction\Container;
use Klarna\Orderlines\Model\Items\Item\Extraction\Coordinator;
use Klarna\Orderlines\Model\Items\Item\Extraction\ShippingAttributes\EntityManager;
use Klarna\Orderlines\Model\Items\Item\Extraction\ShippingAttributes\ProductCollection;
use Magento\Quote\Model\Quote;
use Magento\Catalog\Model\Product;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Orderlines\Model\Items\Item\Extraction\Coordinator
 */
class CoordinatorTest extends TestCase
{
    /**
     * @var Coordinator
     */
    private Coordinator $model;
    /**
     * @var Container
     */
    private Container $container;
    /**
     * @var Country|MockObject
     */
    private Country $country;
    /**
     * @var ExclusiveTaxCalculator|MockObject
     */
    private ExclusiveTaxCalculator $exclusiveTaxCalculator;
    /**
     * @var InclusiveTaxCalculator|MockObject
     */
    private InclusiveTaxCalculator $inclusiveTaxCalculator;
    /**
     * @var ProductCollection|MockObject
     */
    private ProductCollection $productCollection;
    /**
     * @var EntityManager|MockObject
     */
    private EntityManager $entityManager;
    /**
     * @var DataHolder
     */
    private DataHolder $dataHolder;
    /**
     * @var Quote
     */
    private Quote $magentoQuote;
    /**
     * @var Product
     */
    private Product $product;

    public function testCreateOrderLineItemIsUsCountryCalculatingExclusiveTaxes(): void
    {
        $expected = ['abc' => 'def'];
        $this->country->method('isUsCountry')
            ->willReturn(true);
        $this->exclusiveTaxCalculator->method('getOrderLineItem')
            ->willReturn($expected);

        static::assertEquals(
            $expected,
            $this->model->createOrderLineItem($this->container, $this->magentoQuote, $this->dataHolder)
        );
    }

    public function testCreateOrderLineItemIsNotUsCountryCalculatingInclusiveTaxes(): void
    {
        $expected = ['abc' => 'def'];
        $this->country->method('isUsCountry')
            ->willReturn(false);
        $this->inclusiveTaxCalculator->method('getOrderLineItem')
            ->willReturn($expected);

        static::assertEquals(
            $expected,
            $this->model->createOrderLineItem($this->container, $this->magentoQuote, $this->dataHolder)
        );
    }

    public function testCreateOrderLineItemAttachingEntitiesToTheItem(): void
    {
        $this->country->method('isUsCountry')
            ->willReturn(false);
        $this->inclusiveTaxCalculator->method('getOrderLineItem')
            ->willReturn([]);
        $this->productCollection->method('get')
            ->willReturn([$this->product]);

        $expected = ['abc' => 'def'];
        $this->entityManager->method('attachToItem')
            ->willReturn($expected);

        static::assertEquals(
            $expected,
            $this->model->createOrderLineItem($this->container, $this->magentoQuote, $this->dataHolder)
        );
    }

    protected function setUp(): void
    {
        $this->country = $this->createStub(Country::class);
        $this->exclusiveTaxCalculator = $this->createStub(ExclusiveTaxCalculator::class);
        $this->inclusiveTaxCalculator = $this->createStub(InclusiveTaxCalculator::class);
        $this->productCollection = $this->createStub(ProductCollection::class);
        $this->entityManager = $this->createStub(EntityManager::class);
        $this->model = new Coordinator(
            $this->country,
            $this->exclusiveTaxCalculator,
            $this->inclusiveTaxCalculator,
            $this->productCollection,
            $this->entityManager
        );

        $this->container = $this->createStub(Container::class);
        $this->dataHolder = $this->createStub(DataHolder::class);
        $this->magentoQuote = $this->createStub(Quote::class);
        $this->product = $this->createStub(Product::class);
    }
}
