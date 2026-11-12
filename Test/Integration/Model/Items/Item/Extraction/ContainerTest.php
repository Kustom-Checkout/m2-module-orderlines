<?php
/**
 * Copyright © Kustom AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Test\Integration\Model\Items\Item\Extraction;

use Klarna\Orderlines\Model\Items\Item\Extraction\Container;
use Magento\Catalog\Model\Product;
use Magento\Framework\UrlInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Extraction container filled from a real quote item, product and store
 *
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class ContainerTest extends TestCase
{
    /**
     * @var Container
     */
    private Container $model;
    /**
     * @var Item
     */
    private Item $item;
    /**
     * @var Item
     */
    private Item $parentItem;
    /**
     * @var StoreInterface
     */
    private StoreInterface $store;
    /**
     * @var Product
     */
    private Product $product;

    public function testSetValuesOriginalPriceIsEmptyImpliesReturningOriginalPriceOfZero(): void
    {
        $this->model->setValues($this->item, $this->store);
        static::assertEquals(0, $this->model->getOriginalPrice());
    }

    public function testSetValuesOriginalPriceIsNotEmptyImpliesReturningNotZeroOriginalPrice(): void
    {
        $expected = 123;
        $this->item->setData('base_original_price', $expected);
        $this->model->setValues($this->item, $this->store);
        static::assertEquals($expected, $this->model->getOriginalPrice());
    }

    public function testSetValuesPriceIsEmptyImpliesReturningPriceOfZero(): void
    {
        $this->model->setValues($this->item, $this->store);
        static::assertEquals(0, $this->model->getPrice());
    }

    public function testSetValuesPriceIsNotEmptyImpliesReturningNotZeroPrice(): void
    {
        $expected = 123;
        $this->item->setData('base_price', $expected);
        $this->model->setValues($this->item, $this->store);
        static::assertEquals($expected, $this->model->getPrice());
    }

    public function testSetValuesBasePriceInclTaxIsEmptyImpliesReturningPriceIncludedTaxOfZero(): void
    {
        $this->model->setValues($this->item, $this->store);
        static::assertEquals(0, $this->model->getPriceIncludedTax());
    }

    public function testSetValuesBasePriceInclTaxIsNotEmptyImpliesReturningNotZeroPriceIncludedTax(): void
    {
        $expected = 123;
        $this->item->setData('base_price_incl_tax', $expected);
        $this->model->setValues($this->item, $this->store);
        static::assertEquals($expected, $this->model->getPriceIncludedTax());
    }

    public function testSetValuesBaseRowTotalIsEmptyImpliesReturningRowTotalOfZero(): void
    {
        $this->model->setValues($this->item, $this->store);
        static::assertEquals(0, $this->model->getRowTotal());
    }

    public function testSetValuesBaseRowTotalIsNotEmptyImpliesReturningNotZeroRowTotal(): void
    {
        $expected = 123;
        $this->item->setData('base_row_total', $expected);
        $this->model->setValues($this->item, $this->store);
        static::assertEquals($expected, $this->model->getRowTotal());
    }

    public function testSetValuesBaseRowTotalInclTaxIsEmptyImpliesReturningRowTotalIncludedTaxOfZero(): void
    {
        $this->model->setValues($this->item, $this->store);
        static::assertEquals(0, $this->model->getRowTotalIncludedTax());
    }

    public function testSetValuesBaseRowTotalInclTaxIsNotEmptyImpliesReturningNotZeroRowTotalIncludedTax(): void
    {
        $this->item->setData('base_row_total', 10);
        $this->item->setData('base_tax_amount', 11);
        $this->item->setData('discount_tax_compensation_amount', 12);
        $this->model->setValues($this->item, $this->store);
        static::assertEquals(33, $this->model->getRowTotalIncludedTax());
    }

    public function testSetValuesBaseTaxAmountIsEmptyImpliesReturningTaxAmountOfZero(): void
    {
        $this->model->setValues($this->item, $this->store);
        static::assertEquals(0, $this->model->getTaxAmount());
    }

    public function testSetValuesBaseTaxAmountIsNotEmptyImpliesReturningNotZeroTaxAmount(): void
    {
        $expected = 123;
        $this->item->setData('base_tax_amount', $expected);
        $this->model->setValues($this->item, $this->store);
        static::assertEquals($expected, $this->model->getTaxAmount());
    }

    public function testSetValuesBaseDiscountAmountIsEmptyImpliesReturningDiscountAmountOfZero(): void
    {
        $this->model->setValues($this->item, $this->store);
        static::assertEquals(0, $this->model->getDiscountAmount());
    }

    public function testSetValuesBaseDiscountAmountIsNotEmptyImpliesReturningNotZeroDiscountAmount(): void
    {
        $expected = 123;
        $this->item->setData('base_discount_amount', $expected);
        $this->model->setValues($this->item, $this->store);
        static::assertEquals($expected, $this->model->getDiscountAmount());
    }

    public function testSetValuesSettingTheNameAndReturningIt(): void
    {
        $expected = 'item_name';
        $this->item->setData('name', $expected);

        $this->model->setValues($this->item, $this->store);
        static::assertEquals($expected, $this->model->getName());
    }

    public function testSetValuesSettingTheQtyAndInvoiceRefundItemQtyIsGreaterZeroReturningInvoiceRefundItemQty(): void
    {
        $expected = 3;
        $this->item->setData('current_invoice_refund_item_qty', $expected);
        $this->item->setData('qty', 2);
        $this->item->setData('qty_ordered', 1);

        $this->model->setValues($this->item, $this->store);
        static::assertEquals($expected, $this->model->getQty());
    }

    public function testSetValuesSettingTheQtyAndInvoiceRefundItemQtyIsZeroAndQtyNotZeroReturningQty(): void
    {
        $expected = 2;
        $this->item->setData('qty', $expected);
        $this->item->setData('qty_ordered', 1);

        $this->model->setValues($this->item, $this->store);
        static::assertEquals($expected, $this->model->getQty());
    }

    public function testSetValuesSettingTheQtyAndInvoiceRefundItemQtyIsZeroAndQtyIsZeroReturningQtyOrdered(): void
    {
        $expected = 1;
        $this->item->setData('qty_ordered', $expected);

        $this->model->setValues($this->item, $this->store);
        static::assertEquals($expected, $this->model->getQty());
    }

    public function testSetValuesSettingTheSkuAndReturningIt(): void
    {
        $expected = 'sku_id';
        $this->item->setData('sku', $expected);

        $this->model->setValues($this->item, $this->store);
        static::assertEquals($expected, $this->model->getSku());
    }

    public function testSetValuesTaxPercentIsEmptyImpliesReturningTaxPercentOfZero(): void
    {
        $this->model->setValues($this->item, $this->store);
        static::assertEquals(0, $this->model->getTaxPercent());
    }

    public function testSetValuesTaxPercentIsNotEmptyImpliesReturningNotZeroTaxPercent(): void
    {
        $expected = 123;
        $this->item->setData('tax_percent', $expected);
        $this->model->setValues($this->item, $this->store);
        static::assertEquals($expected, $this->model->getTaxPercent());
    }

    public function testSetValuesSettingTheProductAndReturningIt(): void
    {
        $this->model->setValues($this->item, $this->store);
        static::assertSame($this->product, $this->model->getProduct());
    }

    public function testSetValuesSettingTheProductTypeAndReturningIt(): void
    {
        $this->model->setValues($this->item, $this->store);
        static::assertEquals('abc', $this->model->getProductType());
    }

    public function testSetValuesSettingTheProductUrlAndReturningIt(): void
    {
        $expected = $this->product->getUrlInStore();

        $this->model->setValues($this->item, $this->store);
        static::assertNotEmpty($expected);
        static::assertEquals($expected, $this->model->getProductUrl());
    }

    public function testSetValuesSettingTheImageUrlButProductHasNoImageImpliesImageUrlIsEmpty(): void
    {
        $this->model->setValues($this->item, $this->store);
        static::assertEquals('', $this->model->getImageUrl());
    }

    public function testSetValuesSettingTheImageUrlAndProductHasImageImpliesImageUrlIsNotEmpty(): void
    {
        $this->product->setData('small_image', '/my_image.jpg');

        $this->model->setValues($this->item, $this->store);
        static::assertEquals(
            $this->store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'catalog/product/my_image.jpg',
            $this->model->getImageUrl()
        );
    }

    public function testSetValuesSettingStoreInstanceAndReturningIt(): void
    {
        $this->model->setValues($this->item, $this->store);
        static::assertSame($this->store, $this->model->getStore());
    }

    public function testSetValuesItemsIsProductOfBundledProductImpliesUsingQtyOfParentBundledProduct(): void
    {
        $this->item->setData('qty', 1);
        $this->item->setParentItem($this->parentItem);
        $this->parentItem->setData('qty', 3);
        $this->model->setValues($this->item, $this->store);
        static::assertEquals(3, $this->model->getQty());
    }

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->model = $objectManager->create(Container::class);
        $this->store = $objectManager->get(StoreManagerInterface::class)->getStore();

        $this->product = $objectManager->create(Product::class);
        $this->product->setData('store_id', $this->store->getId());
        $this->product->setData('url_key', 'my-product');
        $this->product->setTypeId('abc');

        $this->item = $objectManager->create(Item::class);
        $this->item->setData('product', $this->product);

        $this->parentItem = $objectManager->create(Item::class);
    }
}
