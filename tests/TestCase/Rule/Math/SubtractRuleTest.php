<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Rule\Math;

use PHPUnit\Framework\TestCase;
use RuleFlow\Rule\JsonLogicRuleFactory;
use RuleFlow\Rule\Math\SubtractRule;
use RuleFlow\Rule\Variable\VarRule;

/**
 * SubtractRule Test
 */
class SubtractRuleTest extends TestCase
{
    /**
     * Test constructor and getters
     *
     * @return void
     */
    public function testConstructorAndGetters(): void
    {
        $values = [10, 3];

        $subtractRule = new SubtractRule($values);

        $this->assertEquals('-', $subtractRule->getOperator());
        $this->assertSame($values, $subtractRule->getValues());
    }

    /**
     * Test setValues method
     *
     * @return void
     */
    public function testSetValues(): void
    {
        $values1 = [5, 2];
        $subtractRule = new SubtractRule($values1);
        $this->assertSame($values1, $subtractRule->getValues());

        $values2 = [10, 5];
        $subtractRule->setValues($values2);
        $this->assertSame($values2, $subtractRule->getValues());
    }

    /**
     * Test addValue method
     *
     * @return void
     */
    public function testAddValue(): void
    {
        $subtractRule = new SubtractRule([10]);
        $this->assertCount(1, $subtractRule->getValues());

        $subtractRule->addValue(3);
        $this->assertCount(2, $subtractRule->getValues());
        $this->assertSame(3, $subtractRule->getValues()[1]);

        $subtractRule->addValue(2);
        $this->assertCount(3, $subtractRule->getValues());
        $this->assertSame(2, $subtractRule->getValues()[2]);
    }

    /**
     * Test toArray method with simple values for binary subtraction
     *
     * @return void
     */
    public function testToArraySimpleBinarySubtraction(): void
    {
        // Example: {"−": [10, 3]} -> 7
        $subtractRule = new SubtractRule([10, 3]);

        $expected = [
            '-' => [10, 3],
        ];

        $this->assertEquals($expected, $subtractRule->toArray());
    }

    /**
     * Test unary negation (single value)
     *
     * @return void
     */
    public function testUnaryNegation(): void
    {
        // Example: {"−": [5]} -> -5 (unary negation)
        $subtractRule = new SubtractRule([5]);

        $expected = [
            '-' => [5],
        ];

        $this->assertEquals($expected, $subtractRule->toArray());
    }

    /**
     * Test with multiple values
     *
     * @return void
     */
    public function testWithMultipleValues(): void
    {
        // Example: {"−": [10, 3, 2]} -> 5 (10 - 3 - 2)
        $subtractRule = new SubtractRule([10, 3, 2]);

        $expected = [
            '-' => [10, 3, 2],
        ];

        $this->assertEquals($expected, $subtractRule->toArray());
    }

    /**
     * Test with variables
     *
     * @return void
     */
    public function testWithVariables(): void
    {
        // Example: Calculate cart total minus discount
        $subtractRule = new SubtractRule([
            new VarRule('cart.total'),
            new VarRule('cart.discount'),
        ]);

        $expected = [
            '-' => [
                ['var' => 'cart.total'],
                ['var' => 'cart.discount'],
            ],
        ];

        $this->assertEquals($expected, $subtractRule->toArray());
    }

    /**
     * Test with mixed values
     *
     * @return void
     */
    public function testWithMixedValues(): void
    {
        // Example: Calculate cart total minus fixed discount
        $subtractRule = new SubtractRule([
            new VarRule('cart.total'),
            10,
        ]);

        $expected = [
            '-' => [
                ['var' => 'cart.total'],
                10,
            ],
        ];

        $this->assertEquals($expected, $subtractRule->toArray());
    }

    /**
     * Test using factory methods
     *
     * @return void
     */
    public function testFactoryMethods(): void
    {
        $rule = JsonLogicRuleFactory::subtract([
            JsonLogicRuleFactory::var('total'),
            JsonLogicRuleFactory::var('discount'),
            5,
        ]);

        $expected = [
            '-' => [
                ['var' => 'total'],
                ['var' => 'discount'],
                5,
            ],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }

    /**
     * Test fluid interface
     *
     * @return void
     */
    public function testFluidInterface(): void
    {
        $subtractRule = new SubtractRule([10]);

        $returnedRule = $subtractRule
            ->addValue(3)
            ->setValues([20, 5]);

        $this->assertSame($subtractRule, $returnedRule);
        $this->assertEquals([20, 5], $subtractRule->getValues());
    }

    /**
     * Test remaining balance scenario
     *
     * @return void
     */
    public function testRemainingBalanceScenario(): void
    {
        // Example: Calculate remaining budget after expenses
        $subtractRule = new SubtractRule([
            new VarRule('budget.total'),
            new VarRule('expenses.current'),
        ]);

        $expected = [
            '-' => [
                ['var' => 'budget.total'],
                ['var' => 'expenses.current'],
            ],
        ];

        $this->assertEquals($expected, $subtractRule->toArray());
    }
}
