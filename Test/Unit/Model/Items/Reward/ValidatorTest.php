<?php
/**
 * Copyright © Kustom AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Test\Unit\Model\Items\Reward;

use Klarna\Orderlines\Model\Container\DataHolder;
use Klarna\Orderlines\Model\Container\Parameter;
use Klarna\Orderlines\Model\Items\Reward\Validator;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Klarna\Orderlines\Model\Items\Reward\Validator
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

    public function testIsCollectableRewardKeyIsNotSetImpliesResultFalse(): void
    {
        $this->dataHolder->method('getTotals')
            ->willReturn([]);

        static::assertFalse($this->validator->isCollectable($this->dataHolder));
    }

    public function testIsCollectableRewardKeyIsSetImpliesResultTrue(): void
    {
        $this->dataHolder->method('getTotals')
            ->willReturn(['reward' => 1]);

        static::assertTrue($this->validator->isCollectable($this->dataHolder));
    }

    public function testIsFetchableRewardTotalAmountIsLowerZeroImpliesResultFalse(): void
    {
        $this->parameter->method('getRewardTotalAmount')
            ->willReturn(-1);

        static::assertFalse($this->validator->isFetchable($this->parameter));
    }

    public function testIsFetchableRewardTotalAmountIsZeroImpliesResultFalse(): void
    {
        $this->parameter->method('getRewardTotalAmount')
            ->willReturn(0);

        static::assertFalse($this->validator->isFetchable($this->parameter));
    }

    public function testIsFetchableRewardTotalAmountIsGreaterZeroImpliesResultTrue(): void
    {
        $this->parameter->method('getRewardTotalAmount')
            ->willReturn(1);

        static::assertTrue($this->validator->isFetchable($this->parameter));
    }

    protected function setUp(): void
    {
        $this->validator = new Validator();
        $this->dataHolder = $this->createStub(DataHolder::class);
        $this->parameter = $this->createStub(Parameter::class);
    }
}
