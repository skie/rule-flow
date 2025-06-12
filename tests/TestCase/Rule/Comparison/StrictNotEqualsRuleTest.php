<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Rule\Comparison;

use PHPUnit\Framework\TestCase;
use RuleFlow\Rule\Comparison\StrictNotEqualsRule;
use RuleFlow\Rule\JsonLogicRuleFactory;
use RuleFlow\Rule\Variable\VarRule;

/**
 * StrictNotEqualsRule Test
 */
class StrictNotEqualsRuleTest extends TestCase
{
    /**
     * Test constructor and getters
     *
     * @return void
     */
    public function testConstructorAndGetters(): void
    {
        $left = 'status';
        $right = 'inactive';

        $strictNotEqualsRule = new StrictNotEqualsRule($left, $right);

        $this->assertEquals('!==', $strictNotEqualsRule->getOperator());
        $this->assertSame($left, $strictNotEqualsRule->getLeft());
        $this->assertSame($right, $strictNotEqualsRule->getRight());
    }

    /**
     * Test toArray method with simple values
     *
     * @return void
     */
    public function testToArraySimple(): void
    {
        // Example: {"!==": [1, "1"]} - Checks strict inequality (different types)
        $strictNotEqualsRule = new StrictNotEqualsRule(1, '1');

        $expected = [
            '!==' => [1, '1'],
        ];

        $this->assertEquals($expected, $strictNotEqualsRule->toArray());
    }

    /**
     * Test with variable on left side
     *
     * @return void
     */
    public function testWithVariableOnLeft(): void
    {
        // Example: {"!==": [{"var": "user.status"}, "inactive"]}
        $varRule = new VarRule('user.status');
        $strictNotEqualsRule = new StrictNotEqualsRule($varRule, 'inactive');

        $expected = [
            '!==' => [
                ['var' => 'user.status'],
                'inactive',
            ],
        ];

        $this->assertEquals($expected, $strictNotEqualsRule->toArray());
    }

    /**
     * Test with variable on right side
     *
     * @return void
     */
    public function testWithVariableOnRight(): void
    {
        // Example: {"!==": ["inactive", {"var": "user.status"}]}
        $varRule = new VarRule('user.status');
        $strictNotEqualsRule = new StrictNotEqualsRule('inactive', $varRule);

        $expected = [
            '!==' => [
                'inactive',
                ['var' => 'user.status'],
            ],
        ];

        $this->assertEquals($expected, $strictNotEqualsRule->toArray());
    }

    /**
     * Test with variables on both sides
     *
     * @return void
     */
    public function testWithVariablesOnBothSides(): void
    {
        // Example: {"!==": [{"var": "requested.role"}, {"var": "current.role"}]}
        $leftVar = new VarRule('requested.role');
        $rightVar = new VarRule('current.role');
        $strictNotEqualsRule = new StrictNotEqualsRule($leftVar, $rightVar);

        $expected = [
            '!==' => [
                ['var' => 'requested.role'],
                ['var' => 'current.role'],
            ],
        ];

        $this->assertEquals($expected, $strictNotEqualsRule->toArray());
    }

    /**
     * Test with null comparison
     *
     * @return void
     */
    public function testWithNullComparison(): void
    {
        // Example: Check if a variable is strictly not null
        $varRule = new VarRule('user.email');
        $strictNotEqualsRule = new StrictNotEqualsRule($varRule, null);

        $expected = [
            '!==' => [
                ['var' => 'user.email'],
                null,
            ],
        ];

        $this->assertEquals($expected, $strictNotEqualsRule->toArray());
    }

    /**
     * Test with boolean comparison
     *
     * @return void
     */
    public function testWithBooleanComparison(): void
    {
        // Example: Check if flag is strictly not false
        $varRule = new VarRule('flags.isEnabled');
        $strictNotEqualsRule = new StrictNotEqualsRule($varRule, false);

        $expected = [
            '!==' => [
                ['var' => 'flags.isEnabled'],
                false,
            ],
        ];

        $this->assertEquals($expected, $strictNotEqualsRule->toArray());
    }

    /**
     * Test using factory methods
     *
     * @return void
     */
    public function testFactoryMethods(): void
    {
        $rule = JsonLogicRuleFactory::strictNotEquals(
            JsonLogicRuleFactory::var('type'),
            'deleted',
        );

        $expected = [
            '!==' => [
                ['var' => 'type'],
                'deleted',
            ],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }

    /**
     * Test type checking use case
     *
     * @return void
     */
    public function testTypeCheckingUseCase(): void
    {
        // Example: Check that numeric string is not equal to number (type check)
        $strictNotEqualsRule = new StrictNotEqualsRule('42', 42);

        $expected = [
            '!==' => ['42', 42],
        ];

        $this->assertEquals($expected, $strictNotEqualsRule->toArray());
    }
}
