<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Rule\Math;

use PHPUnit\Framework\TestCase;
use RuleFlow\Rule\JsonLogicRuleFactory;
use RuleFlow\Rule\Math\ModuloRule;
use RuleFlow\Rule\Variable\VarRule;

/**
 * ModuloRule Test
 */
class ModuloRuleTest extends TestCase
{
    /**
     * Test constructor and getters
     *
     * @return void
     */
    public function testConstructorAndGetters(): void
    {
        $dividend = 10;
        $divisor = 3;

        $moduloRule = new ModuloRule($dividend, $divisor);

        $this->assertEquals('%', $moduloRule->getOperator());
        $this->assertSame($dividend, $moduloRule->getDividend());
        $this->assertSame($divisor, $moduloRule->getDivisor());
    }

    /**
     * Test with rule values
     *
     * @return void
     */
    public function testWithRuleValues(): void
    {
        $dividend = new VarRule('id');
        $divisor = new VarRule('pageSize');

        $moduloRule = new ModuloRule($dividend, $divisor);

        $this->assertSame($dividend, $moduloRule->getDividend());
        $this->assertSame($divisor, $moduloRule->getDivisor());
    }

    /**
     * Test toArray method with simple values
     *
     * @return void
     */
    public function testToArraySimpleValues(): void
    {
        $moduloRule = new ModuloRule(10, 3);

        $expected = [
            '%' => [10, 3],
        ];

        $this->assertEquals($expected, $moduloRule->toArray());
    }

    /**
     * Test toArray method with rule values
     *
     * @return void
     */
    public function testToArrayRuleValues(): void
    {
        $dividend = new VarRule('id');
        $divisor = new VarRule('pageSize');

        $moduloRule = new ModuloRule($dividend, $divisor);

        $expected = [
            '%' => [
                ['var' => 'id'],
                ['var' => 'pageSize'],
            ],
        ];

        $this->assertEquals($expected, $moduloRule->toArray());
    }

    /**
     * Test toArray method with mixed values
     *
     * @return void
     */
    public function testToArrayMixedValues(): void
    {
        $dividend = new VarRule('index');
        $divisor = 2;

        $moduloRule = new ModuloRule($dividend, $divisor);

        $expected = [
            '%' => [
                ['var' => 'index'],
                2,
            ],
        ];

        $this->assertEquals($expected, $moduloRule->toArray());
    }

    /**
     * Test factory methods
     *
     * @return void
     */
    public function testFactoryMethods(): void
    {
        $rule = JsonLogicRuleFactory::modulo(
            JsonLogicRuleFactory::var('value'),
            2,
        );

        $expected = [
            '%' => [
                ['var' => 'value'],
                2,
            ],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }
}
