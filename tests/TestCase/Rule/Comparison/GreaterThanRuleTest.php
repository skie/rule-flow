<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Rule\Comparison;

use PHPUnit\Framework\TestCase;
use RuleFlow\Rule\Comparison\GreaterThanRule;
use RuleFlow\Rule\JsonLogicRuleFactory;
use RuleFlow\Rule\Variable\VarRule;

/**
 * GreaterThan Rule Test
 *
 * Tests for the GreaterThanRule class
 */
class GreaterThanRuleTest extends TestCase
{
    /**
     * Test toArray() with simple values
     *
     * @return void
     */
    public function testToArraySimple(): void
    {
        $rule = new GreaterThanRule(new VarRule('age'), 18);

        $expected = [
            '>' => [['var' => 'age'], 18],
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
        $rule = JsonLogicRuleFactory::greaterThan(
            JsonLogicRuleFactory::var('age'),
            18,
        );

        $expected = [
            '>' => [['var' => 'age'], 18],
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
        $rule = new GreaterThanRule(
            new VarRule('count'),
            new VarRule('min', 0),
        );

        $expected = [
            '>' => [['var' => 'count'], ['var' => ['min', 0]]],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }
}
