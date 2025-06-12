<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Rule;

use PHPUnit\Framework\TestCase;
use RuleFlow\Rule\Comparison\LessThanOrEqualRule;
use RuleFlow\Rule\Comparison\LessThanRule;
use RuleFlow\Rule\JsonLogicRuleFactory;
use RuleFlow\Rule\Variable\VarRule;

/**
 * JsonLogicRuleFactory Test
 */
class JsonLogicRuleFactoryTest extends TestCase
{
    /**
     * Test between method
     *
     * @return void
     */
    public function testBetween(): void
    {
        $min = 0;
        $value = new VarRule('temperature');
        $max = 100;

        $rule = JsonLogicRuleFactory::between($min, $value, $max);

        $this->assertInstanceOf(LessThanRule::class, $rule);
        $this->assertEquals($min, $rule->getFirst());
        $this->assertEquals($value, $rule->getSecond());
        $this->assertEquals($max, $rule->getThird());
        $this->assertTrue($rule->isBetween());

        // Check the generated JSON logic
        $expected = [
            '<' => [0, ['var' => 'temperature'], 100],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }

    /**
     * Test betweenInclusive method
     *
     * @return void
     */
    public function testBetweenInclusive(): void
    {
        $min = 0;
        $value = new VarRule('temperature');
        $max = 100;

        $rule = JsonLogicRuleFactory::betweenInclusive($min, $value, $max);

        $this->assertInstanceOf(LessThanOrEqualRule::class, $rule);
        $this->assertEquals($min, $rule->getFirst());
        $this->assertEquals($value, $rule->getSecond());
        $this->assertEquals($max, $rule->getThird());
        $this->assertTrue($rule->isBetween());

        // Check the generated JSON logic
        $expected = [
            '<=' => [0, ['var' => 'temperature'], 100],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }

    /**
     * Test between with all scalar values
     *
     * @return void
     */
    public function testBetweenWithScalarValues(): void
    {
        $rule = JsonLogicRuleFactory::between(0, 5, 10);

        $expected = [
            '<' => [0, 5, 10],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }

    /**
     * Test betweenInclusive with all scalar values
     *
     * @return void
     */
    public function testBetweenInclusiveWithScalarValues(): void
    {
        $rule = JsonLogicRuleFactory::betweenInclusive(0, 5, 10);

        $expected = [
            '<=' => [0, 5, 10],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }
}
