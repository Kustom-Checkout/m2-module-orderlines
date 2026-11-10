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
use Klarna\Orderlines\Model\Items\Shipping\Validator;
use Magento\Quote\Model\Quote\Address;
use Magento\Customer\Model\Address as CustomerAddress;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Orderlines\Model\Items\Shipping\Validator
 */
class ValidatorTest extends TestCase
{
    /**
     * @var Validator
     */
    private Validator $validator;

    /**
     * @var DataHolder
     */
    private DataHolder $dataHolder;

    /**
     * @var Parameter
     */
    private Parameter $parameter;

    /**
     * @var CustomerAddress
     */
    private CustomerAddress $address;

    public function testIsCollectableForPrePurchaseReturnFalseIfGetTotalShippingIsNotSet(): void
    {
        // This makes the isCollectableForPrePurchase return true
        $this->parameter->method('isShippingLineEnabled')
            ->willReturn(true);
        $this->dataHolder->method('isVirtual')
            ->willReturn(false);
        $this->dataHolder->method('getShippingAddress')
            ->willReturn($this->createStub(Address::class));

        // This makes the isCollectableForPrePurchase return false
        $this->dataHolder->method('getTotals')
            ->willReturn([]);

        $this->assertFalse(
            $this->validator->isCollectableForPrePurchase($this->dataHolder, $this->parameter)
        );
    }

    public function testIsCollectableForPrePurchaseReturnFalseIfIsVirtual(): void
    {
        // This makes the isCollectableForPrePurchase return true
        $this->parameter->method('isShippingLineEnabled')
            ->willReturn(true);
        $this->dataHolder->method('getTotals')
            ->willReturn(['shipping' => 1]);
        $this->dataHolder->method('getShippingAddress')
            ->willReturn($this->createStub(Address::class));

        // This makes the isCollectableForPrePurchase return false
        $this->dataHolder->method('isVirtual')
            ->willReturn(true);

        $this->assertFalse(
            $this->validator->isCollectableForPrePurchase($this->dataHolder, $this->parameter)
        );
    }

    public function testIsCollectableForPrePurchaseReturnFalseIfIsShippingLineNotEnabled(): void
    {
        // This makes the isCollectableForPrePurchase return true
        $this->dataHolder->method('isVirtual')
            ->willReturn(false);
        $this->dataHolder->method('getTotals')
            ->willReturn(['shipping' => 1]);
        $this->dataHolder->method('getShippingAddress')
            ->willReturn($this->createStub(Address::class));

        // This makes the isCollectableForPrePurchase return false
        $this->parameter->method('isShippingLineEnabled')
            ->willReturn(false);

        $this->assertFalse(
            $this->validator->isCollectableForPrePurchase($this->dataHolder, $this->parameter)
        );
    }

    public function testIsCollectableForPrePurchaseReturnFalseIfGetShippingAddressIsNull(): void
    {
        // This makes the isCollectableForPrePurchase return true
        $this->parameter->method('isShippingLineEnabled')
            ->willReturn(true);
        $this->dataHolder->method('isVirtual')
            ->willReturn(false);
        $this->dataHolder->method('getTotals')
            ->willReturn(['shipping' => 1]);

        // This makes the isCollectableForPrePurchase return false
        $this->dataHolder->method('getShippingAddress')
            ->willReturn(null);

        $this->assertFalse(
            $this->validator->isCollectableForPrePurchase($this->dataHolder, $this->parameter)
        );
    }

    public function testIsCollectableForPostPurchaseNotVirtualAndNotEmptyShippingAddressImpliesReturningTrue(): void
    {
        $this->dataHolder->method('isVirtual')
            ->willReturn(false);
        $this->dataHolder->method('getShippingAddress')
            ->willReturn($this->address);

        static::assertTrue($this->validator->isCollectableForPostPurchase($this->dataHolder));
    }

    public function testIsCollectableForPostPurchaseVirtualAndNotEmptyShippingAddressImpliesReturningFalse(): void
    {
        $this->dataHolder->method('isVirtual')
            ->willReturn(true);
        $this->dataHolder->method('getShippingAddress')
            ->willReturn($this->address);

        static::assertFalse($this->validator->isCollectableForPostPurchase($this->dataHolder));
    }

    public function testIsCollectableForPostPurchaseNotVirtualAndEmptyShippingAddressImpliesReturningFalse(): void
    {
        $this->dataHolder->method('isVirtual')
            ->willReturn(false);
        $this->dataHolder->method('getShippingAddress')
            ->willReturn(null);

        static::assertFalse($this->validator->isCollectableForPostPurchase($this->dataHolder));
    }

    public function testIsCollectableForPostPurchaseVirtualAndEmptyShippingAddressImpliesReturningFalse(): void
    {
        $this->dataHolder->method('isVirtual')
            ->willReturn(true);
        $this->dataHolder->method('getShippingAddress')
            ->willReturn(null);

        static::assertFalse($this->validator->isCollectableForPostPurchase($this->dataHolder));
    }

    protected function setUp(): void
    {
        $this->validator = new Validator();

        $this->dataHolder = $this->createStub(DataHolder::class);
        $this->parameter = $this->createStub(Parameter::class);

        $this->address = $this->createStub(CustomerAddress::class);
    }
}
