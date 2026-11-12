<?php
/**
 * Copyright © Kustom AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Test\Unit\Model\Calculator\ItemShippingAttributes;

use Klarna\Orderlines\Model\Calculator\ItemShippingAttributes\Weight;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Orderlines\Model\Calculator\ItemShippingAttributes\Weight
 */
class WeightTest extends TestCase
{
    /**
     * @var Weight
     */
    private Weight $weight;
    /**
     * @var Product
     */
    private Product $product;
    /**
     * @var StoreInterface
     */
    private StoreInterface $store;
    /**
     * @var ScopeConfigInterface&\PHPUnit\Framework\MockObject\Stub
     */
    private $scopeConfig;

    public function testGetNoUnitValueWasFoundImpliesDefaultOneIsUsed(): void
    {
        $this->scopeConfig->method('getValue')
            ->willReturn(null);
        $this->product->method('getWeight')
            ->willReturn('100');

        static::assertEquals(100000, $this->weight->get($this->product));
    }

    public function testGetUnknownUnitValueWasFoundImpliesDefaultOneIsUsed(): void
    {
        $this->scopeConfig->method('getValue')
            ->willReturn('abc');
        $this->product->method('getWeight')
            ->willReturn('100');

        static::assertEquals(100000, $this->weight->get($this->product));
    }

    public function testGetLbsUnitValueWasFoundImpliesUsingLbsWeightCalculatorValue(): void
    {
        $this->scopeConfig->method('getValue')
            ->willReturn('lbs');
        $this->product->method('getWeight')
            ->willReturn('100');

        static::assertEquals(45360, $this->weight->get($this->product));
    }

    protected function setUp(): void
    {
        $this->scopeConfig = $this->createStub(ScopeConfigInterface::class);
        $this->weight = new Weight($this->scopeConfig);
        $this->product = $this->createStub(Product::class);
        $this->store = $this->createStub(StoreInterface::class);

        $this->product->method('getStore')
            ->willReturn($this->store);
    }
}
