<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Rule\Comparison;

use PHPUnit\Framework\TestCase;
use RuleFlow\Rule\Comparison\LessThanRule;
use RuleFlow\Rule\JsonLogicRuleFactory;
use RuleFlow\Rule\Variable\VarRule;

/**
 * LessThan Rule Test
 *
 * Tests for the LessThanRule class
 */
class LessThanRuleTest extends TestCase
{
    /**
     * Test constructor and getters
     *
     * @return void
     */
    public function testConstructorAndGetters(): void
    {
        $first = 1;
        $second = 10;

        $lessThanRule = new LessThanRule($first, $second);

        $this->assertEquals('<', $lessThanRule->getOperator());
        $this->assertSame($first, $lessThanRule->getFirst());
        $this->assertSame($second, $lessThanRule->getSecond());
        $this->assertNull($lessThanRule->getThird());
        $this->assertFalse($lessThanRule->isBetween());
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

        $lessThanRule = new LessThanRule($first, $second, $third);

        $this->assertEquals('<', $lessThanRule->getOperator());
        $this->assertSame($first, $lessThanRule->getFirst());
        $this->assertSame($second, $lessThanRule->getSecond());
        $this->assertSame($third, $lessThanRule->getThird());
        $this->assertTrue($lessThanRule->isBetween());
    }

    /**
     * Test toArray() with simple values
     *
     * @return void
     */
    public function testToArraySimple(): void
    {
        $rule = new LessThanRule(5, 10);

        $expected = [
            '<' => [5, 10],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }

    /**
     * Test toArray() with between operation
     *
     * @return void
     */
    public function testToArrayWithBetween(): void
    {
        $rule = new LessThanRule(1, 5, 10);

        $expected = [
            '<' => [1, 5, 10],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }

    /**
     * Test with variable on left side
     *
     * @return void
     */
    public function testWithVariableOnLeft(): void
    {
        $varRule = new VarRule('temperature');
        $rule = new LessThanRule($varRule, 100);

        $expected = [
            '<' => [
                ['var' => 'temperature'],
                100,
            ],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }

    /**
     * Test with variable for middle in between operation
     *
     * @return void
     */
    public function testWithVariableMiddleInBetween(): void
    {
        $varRule = new VarRule('temperature');
        $rule = new LessThanRule(0, $varRule, 100);

        $expected = [
            '<' => [0, ['var' => 'temperature'], 100],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }

    /**
     * Test using factory method for between
     *
     * @return void
     */
    public function testFactoryMethodForBetween(): void
    {
        $rule = JsonLogicRuleFactory::between(
            0,
            JsonLogicRuleFactory::var('temperature'),
            100,
        );

        $expected = [
            '<' => [0, ['var' => 'temperature'], 100],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }

    /**
     * Test temperature range scenario
     *
     * @return void
     */
    public function testTemperatureRangeScenario(): void
    {
        $varRule = new VarRule('temperature');
        $rule = new LessThanRule(0, $varRule, 100);

        $expected = [
            '<' => [0, ['var' => 'temperature'], 100],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }
}
