<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Rule\Math;

use PHPUnit\Framework\TestCase;
use RuleFlow\Rule\JsonLogicRuleFactory;
use RuleFlow\Rule\Math\DivideRule;
use RuleFlow\Rule\Variable\VarRule;

/**
 * DivideRule Test
 */
class DivideRuleTest extends TestCase
{
    /**
     * Test constructor and getters
     *
     * @return void
     */
    public function testConstructorAndGetters(): void
    {
        $dividend = 10;
        $divisor = 2;

        $divideRule = new DivideRule($dividend, $divisor);

        $this->assertEquals('/', $divideRule->getOperator());
        $this->assertSame($dividend, $divideRule->getDividend());
        $this->assertSame($divisor, $divideRule->getDivisor());
    }

    /**
     * Test with rule values
     *
     * @return void
     */
    public function testWithRuleValues(): void
    {
        $dividend = new VarRule('total');
        $divisor = new VarRule('count');

        $divideRule = new DivideRule($dividend, $divisor);

        $this->assertSame($dividend, $divideRule->getDividend());
        $this->assertSame($divisor, $divideRule->getDivisor());
    }

    /**
     * Test toArray method with simple values
     *
     * @return void
     */
    public function testToArraySimpleValues(): void
    {
        $divideRule = new DivideRule(10, 2);

        $expected = [
            '/' => [10, 2],
        ];

        $this->assertEquals($expected, $divideRule->toArray());
    }

    /**
     * Test toArray method with rule values
     *
     * @return void
     */
    public function testToArrayRuleValues(): void
    {
        $dividend = new VarRule('total');
        $divisor = new VarRule('count');

        $divideRule = new DivideRule($dividend, $divisor);

        $expected = [
            '/' => [
                ['var' => 'total'],
                ['var' => 'count'],
            ],
        ];

        $this->assertEquals($expected, $divideRule->toArray());
    }

    /**
     * Test toArray method with mixed values
     *
     * @return void
     */
    public function testToArrayMixedValues(): void
    {
        $dividend = new VarRule('value');
        $divisor = 100;

        $divideRule = new DivideRule($dividend, $divisor);

        $expected = [
            '/' => [
                ['var' => 'value'],
                100,
            ],
        ];

        $this->assertEquals($expected, $divideRule->toArray());
    }

    /**
     * Test factory methods
     *
     * @return void
     */
    public function testFactoryMethods(): void
    {
        $rule = JsonLogicRuleFactory::divide(
            JsonLogicRuleFactory::var('total'),
            JsonLogicRuleFactory::var('count'),
        );

        $expected = [
            '/' => [
                ['var' => 'total'],
                ['var' => 'count'],
            ],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }
}
