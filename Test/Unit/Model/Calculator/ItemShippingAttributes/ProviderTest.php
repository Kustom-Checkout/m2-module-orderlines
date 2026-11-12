<?php
/**
 * Copyright © Kustom AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Test\Unit\Model\Calculator\ItemShippingAttributes;

use Klarna\Orderlines\Model\Calculator\ItemShippingAttributes\Categories;
use Klarna\Orderlines\Model\Calculator\ItemShippingAttributes\Dimensions;
use Klarna\Orderlines\Model\Calculator\ItemShippingAttributes\Provider;
use Klarna\Orderlines\Model\Calculator\ItemShippingAttributes\Weight;
use Magento\Catalog\Api\Data\ProductInterface;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Orderlines\Model\Calculator\ItemShippingAttributes\Provider
 */
class ProviderTest extends TestCase
{
    /**
     * @var Provider
     */
    private Provider $provider;
    /**
     * @var ProductInterface
     */
    private ProductInterface $product;

    public function testAddShippingAttributesWeightValueIsAdded(): void
    {
        static::assertArrayHasKey(
            'weight',
            $this->provider->addShippingAttributes([], $this->product)['shipping_attributes']
        );
    }

    public function testAddShippingAttributesDimensionsValueIsAdded(): void
    {
        static::assertArrayHasKey(
            'dimensions',
            $this->provider->addShippingAttributes([], $this->product)['shipping_attributes']
        );
    }

    public function testAddShippingAttributesTagsValueIsAdded(): void
    {
        static::assertArrayHasKey(
            'tags',
            $this->provider->addShippingAttributes([], $this->product)['shipping_attributes']
        );
    }

    protected function setUp(): void
    {
        $this->provider = new Provider(
            $this->createStub(Weight::class),
            $this->createStub(Dimensions::class),
            $this->createStub(Categories::class)
        );
        $this->product = $this->createStub(ProductInterface::class);
    }
}
