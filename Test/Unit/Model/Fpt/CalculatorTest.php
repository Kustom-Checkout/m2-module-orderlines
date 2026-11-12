<?php
/**
 * Copyright © Kustom AB
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Orderlines\Test\Unit\Model\Fpt;

use Klarna\Orderlines\Model\Fpt\Calculator;
use Klarna\Orderlines\Model\Fpt\Validator;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\DataObject;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CalculatorTest extends TestCase
{
    /**
     * @var Calculator
     */
    private Calculator $calculator;
    /**
     * @var Validator|MockObject
     */
    private Validator|MockObject $validator;

    public function testGetFptDataItemsAreInvalid(): void
    {
        $this->validator
            ->expects(static::never())
            ->method('isValidOrderItem');
        $this->validator
            ->expects(static::never())
            ->method('isValidQuoteItem');

        $expected = [
            'tax' => 0,
            'name' => [],
            'reference' => []
        ];
        $result = $this->calculator->getFptData($this->createData([]));

        static::assertSame($expected, $result);
    }

    public function testGetFptDataItemsWithAndWithoutAppliedWeeeTaxes(): void
    {
        $data = $this->createData([
            $this->createCartItem(12, '{"ref":{"title":"VAT"}}'),
            $this->createCartItem(21, '{"ref":{"no_title_here":""}}'),
            $this->createCartItem(19, '{"ref":{"title":""}}'),
            $this->createCartItem(14, '{"ref":{"title":"VAT", "title_2": "irrelevant"}}')
        ]);

        $this->validator
            ->expects(static::exactly(4))
            ->method('isValidOrderItem')
            ->willReturn(true);
        $this->validator
            ->expects(static::exactly(4))
            ->method('isValidQuoteItem')
            ->willReturn(true);

        $expected = [
            'tax' => 66,
            'name' => ['VAT'],
            'reference' => ['VAT']
        ];
        $result = $this->calculator->getFptData($data);

        static::assertSame($expected, $result);
    }

    protected function setUp(): void
    {
        $this->validator = $this->createMock(Validator::class);
        $this->calculator = new Calculator($this->validator);
    }

    /**
     * Creating the data object holding the cart items
     *
     * @param array $items
     * @return ExtensibleDataInterface
     */
    private function createData(array $items): ExtensibleDataInterface
    {
        return new class($items) implements ExtensibleDataInterface {
            /**
             * @var array
             */
            private array $items;

            /**
             * @param array $items
             */
            public function __construct(array $items)
            {
                $this->items = $items;
            }

            /**
             * Getting back all items
             *
             * @return array
             */
            public function getAllItems(): array
            {
                return $this->items;
            }
        };
    }

    /**
     * Creating a cart item. A real data object, so the magic weee getters work.
     *
     * @param int $rowAmount
     * @param string $taxApplied
     * @return ExtensibleDataInterface
     */
    private function createCartItem(int $rowAmount, string $taxApplied): ExtensibleDataInterface
    {
        return new class([
            'weee_tax_applied_row_amount' => $rowAmount,
            'weee_tax_applied' => $taxApplied
        ]) extends DataObject implements ExtensibleDataInterface {
        };
    }
}
