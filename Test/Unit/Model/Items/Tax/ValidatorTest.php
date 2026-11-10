<?php
/**
 * Copyright © Kustom AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Test\Unit\Model\Items\Tax;

use Klarna\Base\Model\Quote\Address\Country;
use Klarna\Orderlines\Model\Items\Tax\Validator;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Orderlines\Model\Items\Tax\Validator
 */
class ValidatorTest extends TestCase
{
    /**
     * @var Quote
     */
    private Quote $quote;

    public function testIsCollectableConfigurationReturnsTrue(): void
    {
        $country = $this->createStub(Country::class);
        $country->method('isUsCountry')
            ->willReturn(true);
        $validator = new Validator($country);

        static::assertTrue($validator->isCollectable($this->quote));
    }

    public function testIsCollectableConfigurationReturnsFalse(): void
    {
        $country = $this->createStub(Country::class);
        $country->method('isUsCountry')
            ->willReturn(false);
        $validator = new Validator($country);

        static::assertFalse($validator->isCollectable($this->quote));
    }

    public function testIsFetchableConfigurationReturnsTrue(): void
    {
        $country = $this->createStub(Country::class);
        $country->method('isUsCountry')
            ->willReturn(true);
        $validator = new Validator($country);
        $validator->isCollectable($this->quote);

        static::assertTrue($validator->isFetchable());
    }

    public function testIsFetchableConfigurationReturnsFalse(): void
    {
        $country = $this->createStub(Country::class);
        $country->method('isUsCountry')
            ->willReturn(false);
        $validator = new Validator($country);
        $validator->isCollectable($this->quote);

        static::assertFalse($validator->isFetchable());
    }

    protected function setUp(): void
    {
        $this->quote = $this->createStub(Quote::class);
    }
}
