<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Rule\Comparison;

use PHPUnit\Framework\TestCase;
use RuleFlow\Rule\Comparison\LessThanOrEqualRule;
use RuleFlow\Rule\JsonLogicRuleFactory;
use RuleFlow\Rule\Variable\VarRule;

/**
 * LessThanOrEqualRule Test
 */
class LessThanOrEqualRuleTest extends TestCase
{
    /**
     * Test constructor and getters
     *
     * @return void
     */
    public function testConstructorAndGetters(): void
    {
        $first = 'cart.total';
        $second = 100;

        $lessThanOrEqualRule = new LessThanOrEqualRule($first, $second);

        $this->assertEquals('<=', $lessThanOrEqualRule->getOperator());
        $this->assertSame($first, $lessThanOrEqualRule->getFirst());
        $this->assertSame($second, $lessThanOrEqualRule->getSecond());
        $this->assertNull($lessThanOrEqualRule->getThird());
        $this->assertFalse($lessThanOrEqualRule->isBetween());
    }

    /**
     * Test constructor and getters with between operation
     *
     * @return void
     */
    public function testConstructorAndGettersWithBetween(): void
    {
        $first = 1;
        $second = 5;
        $third = 10;

        $lessThanOrEqualRule = new LessThanOrEqualRule($first, $second, $third);

        $this->assertEquals('<=', $lessThanOrEqualRule->getOperator());
        $this->assertSame($first, $lessThanOrEqualRule->getFirst());
        $this->assertSame($second, $lessThanOrEqualRule->getSecond());
        $this->assertSame($third, $lessThanOrEqualRule->getThird());
        $this->assertTrue($lessThanOrEqualRule->isBetween());
    }

    /**
     * Test toArray method with simple values
     *
     * @return void
     */
    public function testToArraySimple(): void
    {
        // Example: {"<=": [18, 18]} - exactly equal
        $lessThanOrEqualRule = new LessThanOrEqualRule(18, 18);

        $expected = [
            '<=' => [18, 18],
        ];

        $this->assertEquals($expected, $lessThanOrEqualRule->toArray());
    }

    /**
     * Test toArray method with between operation
     *
     * @return void
     */
    public function testToArrayWithBetween(): void
    {
        // Example: 1 <= 5 <= 10
        $lessThanOrEqualRule = new LessThanOrEqualRule(1, 5, 10);

        $expected = [
            '<=' => [1, 5, 10],
        ];

        $this->assertEquals($expected, $lessThanOrEqualRule->toArray());
    }

    /**
     * Test with variable on left side
     *
     * @return void
     */
    public function testWithVariableOnLeft(): void
    {
        // Example: Check if cart total is under budget
        $varRule = new VarRule('cart.total');
        $lessThanOrEqualRule = new LessThanOrEqualRule($varRule, 100);

        $expected = [
            '<=' => [
                ['var' => 'cart.total'],
                100,
            ],
        ];

        $this->assertEquals($expected, $lessThanOrEqualRule->toArray());
    }

    /**
     * Test with variable for middle in between operation
     *
     * @return void
     */
    public function testWithVariableMiddleInBetween(): void
    {
        // Example: 0 <= {"var": "temperature"} <= 100
        $varRule = new VarRule('temperature');
        $lessThanOrEqualRule = new LessThanOrEqualRule(0, $varRule, 100);

        $expected = [
            '<=' => [0, ['var' => 'temperature'], 100],
        ];

        $this->assertEquals($expected, $lessThanOrEqualRule->toArray());
    }

    /**
     * Test using factory method for between inclusive
     *
     * @return void
     */
    public function testFactoryMethodForBetweenInclusive(): void
    {
        $rule = JsonLogicRuleFactory::betweenInclusive(
            0,
            JsonLogicRuleFactory::var('temperature'),
            100,
        );

        $expected = [
            '<=' => [0, ['var' => 'temperature'], 100],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }

    /**
     * Test temperature range scenario (inclusive)
     *
     * @return void
     */
    public function testTemperatureRangeScenario(): void
    {
        // Example: Check if temperature is between 0 and 100 (inclusive)
        $varRule = new VarRule('temperature');
        $lessThanOrEqualRule = new LessThanOrEqualRule(0, $varRule, 100);

        $expected = [
            '<=' => [0, ['var' => 'temperature'], 100],
        ];

        $this->assertEquals($expected, $lessThanOrEqualRule->toArray());
    }

    /**
     * Test with variable on right side
     *
     * @return void
     */
    public function testWithVariableOnRight(): void
    {
        // Example: Check if discount is at most the total
        $varRule = new VarRule('cart.total');
        $lessThanOrEqualRule = new LessThanOrEqualRule(50, $varRule);

        $expected = [
            '<=' => [
                50,
                ['var' => 'cart.total'],
            ],
        ];

        $this->assertEquals($expected, $lessThanOrEqualRule->toArray());
    }

    /**
     * Test with variables on both sides
     *
     * @return void
     */
    public function testWithVariablesOnBothSides(): void
    {
        // Example: Check if cart total is under budget limit
        $leftVar = new VarRule('cart.total');
        $rightVar = new VarRule('user.budget');
        $lessThanOrEqualRule = new LessThanOrEqualRule($leftVar, $rightVar);

        $expected = [
            '<=' => [
                ['var' => 'cart.total'],
                ['var' => 'user.budget'],
            ],
        ];

        $this->assertEquals($expected, $lessThanOrEqualRule->toArray());
    }

    /**
     * Test budget compliance use case
     *
     * @return void
     */
    public function testBudgetComplianceUseCase(): void
    {
        // Example: Check if a transaction amount is within spending limit
        $varRule = new VarRule('transaction.amount');
        $lessThanOrEqualRule = new LessThanOrEqualRule($varRule, 1000);

        $expected = [
            '<=' => [
                ['var' => 'transaction.amount'],
                1000,
            ],
        ];

        $this->assertEquals($expected, $lessThanOrEqualRule->toArray());
    }

    /**
     * Test using factory methods
     *
     * @return void
     */
    public function testFactoryMethods(): void
    {
        $rule = JsonLogicRuleFactory::lessThanOrEqual(
            JsonLogicRuleFactory::var('weight'),
            50,
        );

        $expected = [
            '<=' => [
                ['var' => 'weight'],
                50,
            ],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }
}
