<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Rule\Comparison;

use PHPUnit\Framework\TestCase;
use RuleFlow\Rule\Comparison\GreaterThanOrEqualRule;
use RuleFlow\Rule\JsonLogicRuleFactory;
use RuleFlow\Rule\Variable\VarRule;

/**
 * GreaterThanOrEqualRule Test
 */
class GreaterThanOrEqualRuleTest extends TestCase
{
    /**
     * Test constructor and getters
     *
     * @return void
     */
    public function testConstructorAndGetters(): void
    {
        $left = 'user.age';
        $right = 18;

        $greaterThanOrEqualRule = new GreaterThanOrEqualRule($left, $right);

        $this->assertEquals('>=', $greaterThanOrEqualRule->getOperator());
        $this->assertSame($left, $greaterThanOrEqualRule->getLeft());
        $this->assertSame($right, $greaterThanOrEqualRule->getRight());
    }

    /**
     * Test toArray method with simple values
     *
     * @return void
     */
    public function testToArraySimple(): void
    {
        // Example: {">=": [18, 18]} - exactly equal
        $greaterThanOrEqualRule = new GreaterThanOrEqualRule(18, 18);

        $expected = [
            '>=' => [18, 18],
        ];

        $this->assertEquals($expected, $greaterThanOrEqualRule->toArray());
    }

    /**
     * Test with variable on left side
     *
     * @return void
     */
    public function testWithVariableOnLeft(): void
    {
        // Example: Check if age is at least 21
        $varRule = new VarRule('age');
        $greaterThanOrEqualRule = new GreaterThanOrEqualRule($varRule, 21);

        $expected = [
            '>=' => [
                ['var' => 'age'],
                21,
            ],
        ];

        $this->assertEquals($expected, $greaterThanOrEqualRule->toArray());
    }

    /**
     * Test with variable on right side
     *
     * @return void
     */
    public function testWithVariableOnRight(): void
    {
        // Example: Check if minimum age requirement is at most the user's age
        $varRule = new VarRule('user.age');
        $greaterThanOrEqualRule = new GreaterThanOrEqualRule(18, $varRule);

        $expected = [
            '>=' => [
                18,
                ['var' => 'user.age'],
            ],
        ];

        $this->assertEquals($expected, $greaterThanOrEqualRule->toArray());
    }

    /**
     * Test with variables on both sides
     *
     * @return void
     */
    public function testWithVariablesOnBothSides(): void
    {
        // Example: Check if user's score is at least the passing threshold
        $leftVar = new VarRule('user.score');
        $rightVar = new VarRule('passing.threshold');
        $greaterThanOrEqualRule = new GreaterThanOrEqualRule($leftVar, $rightVar);

        $expected = [
            '>=' => [
                ['var' => 'user.score'],
                ['var' => 'passing.threshold'],
            ],
        ];

        $this->assertEquals($expected, $greaterThanOrEqualRule->toArray());
    }

    /**
     * Test age verification use case
     *
     * @return void
     */
    public function testAgeVerificationUseCase(): void
    {
        // Example: Check if a user is at least 18 years old
        $varRule = new VarRule('user.age');
        $greaterThanOrEqualRule = new GreaterThanOrEqualRule($varRule, 18);

        $expected = [
            '>=' => [
                ['var' => 'user.age'],
                18,
            ],
        ];

        $this->assertEquals($expected, $greaterThanOrEqualRule->toArray());
    }

    /**
     * Test using factory methods
     *
     * @return void
     */
    public function testFactoryMethods(): void
    {
        $rule = JsonLogicRuleFactory::greaterThanOrEqual(
            JsonLogicRuleFactory::var('grade'),
            60,
        );

        $expected = [
            '>=' => [
                ['var' => 'grade'],
                60,
            ],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }
}
