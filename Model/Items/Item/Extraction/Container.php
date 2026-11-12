<?php
/**
 * Copyright © Kustom AB (Originally developed by Klarna Bank AB)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Model\Items\Item\Extraction;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Downloadable\Model\Product\Type as DownloadableType;
use Magento\Catalog\Model\Product\Type;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Catalog\Model\Product;
use Magento\Framework\UrlInterface;

/**
 * @internal
 */
class Container
{
    /**
     * @var float
     */
    private float $originalPrice;
    /**
     * @var float
     */
    private float $price;
    /**
     * @var float
     */
    private float $priceIncludedTax;
    /**
     * @var float
     */
    private float $rowTotal;
    /**
     * @var float
     */
    private float $rowTotalIncludedTax;
    /**
     * @var float
     */
    private float $taxAmount;
    /**
     * @var float
     */
    private float $discountAmount;
    /**
     * @var string[]
     */
    private array $meta = [
        'name' => '',
        'sku' => ''
    ];
    /**
     * @var int
     */
    private int $qty;
    /**
     * @var float
     */
    private float $taxPercent;
    /**
     * @var Product
     */
    private Product $product;
    /**
     * @var string
     */
    private string $productType;
    /**
     * @var array
     */
    private array $urls = [
        'product' => '',
        'image' => ''
    ];
    /**
     * @var ItemResolverInterface
     */
    private ItemResolverInterface $itemResolver;
    /**
     * @var StoreInterface
     */
    private StoreInterface $store;

    /**
     * @param ItemResolverInterface $itemResolver
     * @codeCoverageIgnore
     */
    public function __construct(ItemResolverInterface $itemResolver)
    {
        $this->itemResolver = $itemResolver;
    }

    /**
     * Getting back the original price of the item
     *
     * @return float
     */
    public function getOriginalPrice(): float
    {
        return $this->originalPrice;
    }

    /**
     * Setting the original price of the item
     *
     * @param ExtensibleDataInterface $item
     */
    public function setOriginalPrice(ExtensibleDataInterface $item): void
    {
        $this->originalPrice = (float) ($item->getBaseOriginalPrice() ?? 0);
    }

    /**
     * Getting back the price of the item
     *
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * Setting the price of the item
     *
     * @param ExtensibleDataInterface $item
     */
    public function setPrice(ExtensibleDataInterface $item): void
    {
        $this->price = (float) ($item->getBasePrice() ?? 0);
    }

    /**
     * Getting back the price of the item including tax
     *
     * @return float
     */
    public function getPriceIncludedTax(): float
    {
        return $this->priceIncludedTax;
    }

    /**
     * Setting the price of the item including tax
     *
     * @param ExtensibleDataInterface $item
     */
    public function setPriceIncludedTax(ExtensibleDataInterface $item): void
    {
        $this->priceIncludedTax = (float) ($item->getBasePriceInclTax() ?? 0);
    }

    /**
     * Getting back the row total of the item
     *
     * @return float
     */
    public function getRowTotal(): float
    {
        return $this->rowTotal;
    }

    /**
     * Setting the row total of the item
     *
     * @param ExtensibleDataInterface $item
     */
    public function setRowTotal(ExtensibleDataInterface $item): void
    {
        if ($this->isInvoiceOrCreditMemoItem($item)) {
            $this->rowTotal = (float) ($item->getCurrentInvoiceRefundItemBaseRowTotal() ?? 0);
            return;
        }

        $this->rowTotal = (float) ($item->getBaseRowTotal() ?? 0);
    }

    /**
     * Getting back the row total of the item including tax
     *
     * @return float
     */
    public function getRowTotalIncludedTax(): float
    {
        return $this->rowTotalIncludedTax;
    }

    /**
     * Setting the row total of the item including tax
     *
     * @param ExtensibleDataInterface $item
     */
    public function setRowTotalIncludedTax(ExtensibleDataInterface $item): void
    {
        if ($this->isInvoiceOrCreditMemoItem($item)) {
            $this->rowTotalIncludedTax = (float) $item->getCurrentInvoiceRefundItemBaseRowTotal() +
                (float) $item->getCurrentInvoiceRefundItemBaseTaxAmount() +
                (float) $item->getCurrentInvoiceRefundItemBaseDiscountTaxCompensationAmount();
            return;
        }

        $this->rowTotalIncludedTax = (float) (
            $item->getBaseRowTotal() + $item->getBaseTaxAmount() + $item->getDiscountTaxCompensationAmount()
            ?? 0
        ) ;
    }

    /**
     * Getting back the tax amount of the item
     *
     * @return float
     */
    public function getTaxAmount(): float
    {
        return $this->taxAmount;
    }

    /**
     * Setting the tax amount of the item
     *
     * @param ExtensibleDataInterface $item
     */
    public function setTaxAmount(ExtensibleDataInterface $item): void
    {
        if ($this->isInvoiceOrCreditMemoItem($item)) {
            $this->taxAmount = (float) ($item->getCurrentInvoiceRefundItemBaseTaxAmount() ?? 0);
            return;
        }

        $this->taxAmount = (float) ($item->getBaseTaxAmount() ?? 0);
    }

    /**
     * Getting back the discount amount of the item
     *
     * @return float
     */
    public function getDiscountAmount(): float
    {
        return $this->discountAmount;
    }

    /**
     * Setting the discount amount of the item
     *
     * @param ExtensibleDataInterface $item
     */
    public function setDiscountAmount(ExtensibleDataInterface $item): void
    {
        if ($this->isInvoiceOrCreditMemoItem($item)) {
            $this->discountAmount = (float) ($item->getCurrentInvoiceRefundItemBaseDiscountAmount() ?? 0);
            return;
        }

        $this->discountAmount = (float) ($item->getBaseDiscountAmount() ?? 0);
    }

    /**
     * Checking if the item is captured or refunded by an invoice or credit memo (the same condition as setQty())
     *
     * The invoice or credit memo can cover only part of the ordered quantity, so its own amounts must be used.
     *
     * @param ExtensibleDataInterface $item
     * @return bool
     */
    private function isInvoiceOrCreditMemoItem(ExtensibleDataInterface $item): bool
    {
        return $item->getCurrentInvoiceRefundItemQty() > 0;
    }

    /**
     * Getting back the name of the item
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->meta['name'];
    }

    /**
     * Setting the name of the item
     *
     * @param ExtensibleDataInterface $item
     */
    public function setName(ExtensibleDataInterface $item): void
    {
        $this->meta['name'] = $item->getName();
    }

    /**
     * Getting back the quantity of the item
     *
     * @return int
     */
    public function getQty(): int
    {
        return $this->qty;
    }

    /**
     * Setting the quantity of the item
     *
     * @param ExtensibleDataInterface $item
     */
    public function setQty(ExtensibleDataInterface $item): void
    {
        if ($item->getCurrentInvoiceRefundItemQty() > 0) { //TODO: Check type
            $this->qty = (int) $item->getCurrentInvoiceRefundItemQty();
            return;
        }

        $parentItem = $item->getParentItem();
        if ($parentItem !== null) {
            $this->qty = (int) $parentItem->getQty();
            return;
        }

        $qty = $item->getQty();
        if ($qty === null) {
            $qty = $item->getQtyOrdered();
        }

        $this->qty = (int) $qty;
    }

    /**
     * Getting back the SKU of the item
     *
     * @return string
     */
    public function getSku(): string
    {
        return $this->meta['sku'];
    }

    /**
     * Setting the SKU of the item
     *
     * @param ExtensibleDataInterface $item
     */
    public function setSku(ExtensibleDataInterface $item): void
    {
        $this->meta['sku'] = $item->getSku();
    }

    /**
     * Getting back the tax percent of the item
     *
     * @return float
     */
    public function getTaxPercent(): float
    {
        return $this->taxPercent;
    }

    /**
     * Setting the tax percent of the item
     *
     * @param ExtensibleDataInterface $item
     */
    public function setTaxPercent(ExtensibleDataInterface $item): void
    {
        $this->taxPercent = (float) ($item->getTaxPercent() ?? 0);
    }

    /**
     * Getting back the product of the item
     *
     * @return Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * Setting the product of the item
     *
     * @param ExtensibleDataInterface $item
     */
    public function setProduct(ExtensibleDataInterface $item): void
    {
        $this->product = $item->getProduct();
    }

    /**
     * Getting back the product type of the item
     *
     * @return string
     */
    public function getProductType(): string
    {
        return $this->productType;
    }

    /**
     * Setting the product type of the item
     *
     * @param ExtensibleDataInterface $item
     */
    public function setProductType(ExtensibleDataInterface $item): void
    {
        $this->productType = $item->getProductType();
    }

    /**
     * Returns true if the product type is virtual or downloadable
     *
     * @return bool
     */
    public function isProductTypeVirtualOrDownloadable(): bool
    {
        return in_array($this->productType, [Type::TYPE_VIRTUAL, DownloadableType::TYPE_DOWNLOADABLE]);
    }

    /**
     * Getting back the product URL of the item
     *
     * @return string
     */
    public function getProductUrl(): string
    {
        return $this->urls['product'];
    }

    /**
     * Setting the product URL of the item
     *
     * @param ExtensibleDataInterface $item
     */
    public function setProductUrl(ExtensibleDataInterface $item): void
    {
        $basicProduct = $this->getBasicProduct($item);
        if ($basicProduct instanceof ProductInterface) {
            $this->urls['product'] = $basicProduct->getUrlInStore();
        }
    }

    /**
     * Getting back the image URL of the item
     *
     * @return string
     */
    public function getImageUrl(): string
    {
        return $this->urls['image'];
    }

    /**
     * Setting the image URL of the item
     *
     * @param ExtensibleDataInterface $item
     */
    public function setImageUrl(ExtensibleDataInterface $item): void
    {
        $basicProduct = $this->getBasicProduct($item);
        if ($basicProduct instanceof ProductInterface) {
            $url = $this->getBaisProductImageUrl($basicProduct);
            if ($url !== null) {
                $this->urls['image'] = $url;
            }
        }
    }

    /**
     * Getting back the url for the basic product
     *
     * @param ProductInterface $product
     * @return string
     */
    private function getBaisProductImageUrl(ProductInterface $product)
    {
        if (!$product->getSmallImage()) {
            return null;
        }
        $baseUrl = $product->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        return $baseUrl . 'catalog/product' . $product->getSmallImage();
    }

    /**
     * Getting back the basic product
     *
     * @param ExtensibleDataInterface $item
     * @return ProductInterface
     */
    private function getBasicProduct(ExtensibleDataInterface $item): ProductInterface
    {
        if ($item instanceof ItemInterface) {
            return $this->itemResolver->getFinalProduct($item);
        }
        return $item->getProduct();
    }

    /**
     * Getting back the store of the item
     *
     * @return StoreInterface
     */
    public function getStore(): StoreInterface
    {
        return $this->store;
    }

    /**
     * Setting the store of the item
     *
     * @param StoreInterface $store
     */
    public function setStore(StoreInterface $store): void
    {
        $this->store = $store;
    }

    /**
     * Returns true for items that have custom options
     *
     * @return bool
     */
    public function isProductCustomizable(): bool
    {
        $product = $this->getProduct();
        return isset($product->getCustomOptions()['option_ids']) ||
            $product->getTypeId() === BundleType::TYPE_CODE;
    }

    /**
     * Setting all values of the container by the item and store instance
     *
     * @param ExtensibleDataInterface $item
     * @param StoreInterface $store
     */
    public function setValues(ExtensibleDataInterface $item, StoreInterface $store): void
    {
        $this->setOriginalPrice($item);
        $this->setPrice($item);
        $this->setPriceIncludedTax($item);
        $this->setRowTotal($item);
        $this->setRowTotalIncludedTax($item);
        $this->setTaxAmount($item);
        $this->setDiscountAmount($item);
        $this->setName($item);
        $this->setQty($item);
        $this->setSku($item);
        $this->setTaxPercent($item);
        $this->setProduct($item);
        $this->setProductType($item);
        $this->setProductUrl($item);
        $this->setImageUrl($item);
        $this->setStore($store);
    }
}
