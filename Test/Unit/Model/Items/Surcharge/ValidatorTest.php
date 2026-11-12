<?php
/**
 * Copyright © Kustom AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Test\Unit\Model\Items\Surcharge;

use Klarna\Orderlines\Model\Container\Parameter;
use Klarna\Orderlines\Model\Items\Surcharge\Validator;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Orderlines\Model\Items\Surcharge\Validator
 */
class ValidatorTest extends TestCase
{
    /**
     * @var Validator
     */
    private Validator $validator;
    /**
     * @var Parameter
     */
    private Parameter $parameter;

    public function testIsFetchableUnitPriceLowerZeroReturnsFalseResult(): void
    {
        $this->parameter->method('getSurchargeUnitPrice')
            ->willReturn(-1);

        static::assertFalse($this->validator->isFetchable($this->parameter));
    }

    public function testIsFetchableUnitPriceZeroReturnsFalseResult(): void
    {
        $this->parameter->method('getSurchargeUnitPrice')
            ->willReturn(0);

        static::assertFalse($this->validator->isFetchable($this->parameter));
    }

    public function testIsFetchableUnitPriceGreaterZeroReturnsTrueResult(): void
    {
        $this->parameter->method('getSurchargeUnitPrice')
            ->willReturn(1);

        static::assertTrue($this->validator->isFetchable($this->parameter));
    }

    protected function setUp(): void
    {
        $this->validator = new Validator();
        $this->parameter = $this->createStub(Parameter::class);
    }
}
