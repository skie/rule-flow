<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Rule\Comparison;

use PHPUnit\Framework\TestCase;
use RuleFlow\Rule\Comparison\EqualsRule;
use RuleFlow\Rule\JsonLogicRuleFactory;
use RuleFlow\Rule\Variable\VarRule;

/**
 * EqualsRule Test
 */
class EqualsRuleTest extends TestCase
{
    /**
     * Test constructor and getters
     *
     * @return void
     */
    public function testConstructorAndGetters(): void
    {
        $left = 'user.role';
        $right = 'admin';

        $equalsRule = new EqualsRule($left, $right);

        $this->assertEquals('==', $equalsRule->getOperator());
        $this->assertSame($left, $equalsRule->getLeft());
        $this->assertSame($right, $equalsRule->getRight());
    }

    /**
     * Test toArray method with simple values
     *
     * @return void
     */
    public function testToArraySimple(): void
    {
        // Example: {"==": [5, 5]}
        $equalsRule = new EqualsRule(5, 5);

        $expected = [
            '==' => [5, 5],
        ];

        $this->assertEquals($expected, $equalsRule->toArray());
    }

    /**
     * Test with variable on left side
     *
     * @return void
     */
    public function testWithVariableOnLeft(): void
    {
        // Example: {"==": [{"var": "user.role"}, "admin"]}
        $varRule = new VarRule('user.role');
        $equalsRule = new EqualsRule($varRule, 'admin');

        $expected = [
            '==' => [
                ['var' => 'user.role'],
                'admin',
            ],
        ];

        $this->assertEquals($expected, $equalsRule->toArray());
    }

    /**
     * Test with variable on right side
     *
     * @return void
     */
    public function testWithVariableOnRight(): void
    {
        // Example: {"==": ["admin", {"var": "user.role"}]}
        $varRule = new VarRule('user.role');
        $equalsRule = new EqualsRule('admin', $varRule);

        $expected = [
            '==' => [
                'admin',
                ['var' => 'user.role'],
            ],
        ];

        $this->assertEquals($expected, $equalsRule->toArray());
    }

    /**
     * Test with variables on both sides
     *
     * @return void
     */
    public function testWithVariablesOnBothSides(): void
    {
        // Example: {"==": [{"var": "user.id"}, {"var": "post.author_id"}]}
        $leftVar = new VarRule('user.id');
        $rightVar = new VarRule('post.author_id');
        $equalsRule = new EqualsRule($leftVar, $rightVar);

        $expected = [
            '==' => [
                ['var' => 'user.id'],
                ['var' => 'post.author_id'],
            ],
        ];

        $this->assertEquals($expected, $equalsRule->toArray());
    }

    /**
     * Test with different types (type coercion)
     *
     * @return void
     */
    public function testWithDifferentTypes(): void
    {
        // Example: {"==": [1, "1"]} - In JsonLogic this would be true due to type coercion
        $equalsRule = new EqualsRule(1, '1');

        $expected = [
            '==' => [1, '1'],
        ];

        $this->assertEquals($expected, $equalsRule->toArray());
    }

    /**
     * Test using factory methods
     *
     * @return void
     */
    public function testFactoryMethods(): void
    {
        $rule = JsonLogicRuleFactory::equals(
            JsonLogicRuleFactory::var('status'),
            'active',
        );

        $expected = [
            '==' => [
                ['var' => 'status'],
                'active',
            ],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }
}
