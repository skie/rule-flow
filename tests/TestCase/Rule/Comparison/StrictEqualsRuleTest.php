<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Rule\Comparison;

use PHPUnit\Framework\TestCase;
use RuleFlow\Rule\Comparison\StrictEqualsRule;
use RuleFlow\Rule\JsonLogicRuleFactory;
use RuleFlow\Rule\Variable\VarRule;

/**
 * StrictEquals Rule Test
 *
 * Tests for the StrictEqualsRule class
 */
class StrictEqualsRuleTest extends TestCase
{
    /**
     * Test constructor and getters
     *
     * @return void
     */
    public function testConstructorAndGetters(): void
    {
        $left = new VarRule('type');
        $right = 'admin';

        $rule = new StrictEqualsRule($left, $right);

        $this->assertEquals('===', $rule->getOperator());
        $this->assertSame($left, $rule->getLeft());
        $this->assertSame($right, $rule->getRight());
    }

    /**
     * Test toArray() with simple values
     *
     * @return void
     */
    public function testToArraySimple(): void
    {
        $rule = new StrictEqualsRule(new VarRule('type'), 'admin');

        $expected = [
            '===' => [['var' => 'type'], 'admin'],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }

    /**
     * Test using the factory methods
     *
     * @return void
     */
    public function testFactoryMethods(): void
    {
        $rule = JsonLogicRuleFactory::strictEquals(
            JsonLogicRuleFactory::var('type'),
            'admin',
        );

        $expected = [
            '===' => [['var' => 'type'], 'admin'],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }

    /**
     * Test with nested rules
     *
     * @return void
     */
    public function testNestedRules(): void
    {
        $rule = new StrictEqualsRule(
            new VarRule('userType'),
            new VarRule('expectedType'),
        );

        $expected = [
            '===' => [['var' => 'userType'], ['var' => 'expectedType']],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }

    /**
     * Test with scalar values
     *
     * @return void
     */
    public function testScalarValues(): void
    {
        $rule = new StrictEqualsRule(true, true);

        $expected = [
            '===' => [true, true],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }

    /**
     * Test with numeric value and string comparison
     *
     * @return void
     */
    public function testNumericAndStringComparison(): void
    {
        $rule = new StrictEqualsRule(5, '5');

        $expected = [
            '===' => [5, '5'],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }
}
