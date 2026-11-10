<?php
/**
 * Copyright © Kustom AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Test\Unit\Model\Items\Item\Extraction\ShippingAttributes\Entities;

use Klarna\Orderlines\Model\Items\Item\Extraction\ShippingAttributes\Entities\Weight;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Orderlines\Model\Items\Item\Extraction\ShippingAttributes\Entities\Weight
 */
class WeightTest extends TestCase
{
    /**
     * @var Weight
     */
    private Weight $model;
    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;
    /**
     * @var Product
     */
    private Product $product;

    public function testGetUnitIsLbsAndProductWeightIsZeroReturningValueZero(): void
    {
        $this->scopeConfig->method('getValue')
            ->willReturn('lbs');

        static::assertEquals(0, $this->model->get($this->product));
    }

    public function testGetUnitIsLbsAndProductWeightIsNotZeroReturningValueOfNotZero(): void
    {
        $this->scopeConfig->method('getValue')
            ->willReturn('lbs');
        $this->product->method('getWeight')
            ->willReturn(12);

        static::assertEquals(5443, $this->model->get($this->product));
    }

    public function testGetUnitIsUnknownAndProductWeightIsZeroReturningValueZero(): void
    {
        $this->scopeConfig->method('getValue')
            ->willReturn('__');

        static::assertEquals(0, $this->model->get($this->product));
    }

    public function testGetUnitIsUnknownAndProductWeightIsNotZeroReturningValueOfNotZero(): void
    {
        $this->scopeConfig->method('getValue')
            ->willReturn('__');
        $this->product->method('getWeight')
            ->willReturn(12);

        static::assertEquals(12000, $this->model->get($this->product));
    }

    protected function setUp(): void
    {
        $this->scopeConfig = $this->createStub(ScopeConfigInterface::class);
        $this->model = new Weight($this->scopeConfig);
        $this->product = $this->createStub(Product::class);
    }
}
