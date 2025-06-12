<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Rule\Math;

use PHPUnit\Framework\TestCase;
use RuleFlow\Rule\JsonLogicRuleFactory;
use RuleFlow\Rule\Math\AddRule;
use RuleFlow\Rule\Variable\VarRule;

/**
 * AddRule Test
 */
class AddRuleTest extends TestCase
{
    /**
     * Test constructor and getters
     *
     * @return void
     */
    public function testConstructorAndGetters(): void
    {
        $values = [5, 10, new VarRule('extra')];

        $addRule = new AddRule($values);

        $this->assertEquals('+', $addRule->getOperator());
        $this->assertSame($values, $addRule->getValues());
    }

    /**
     * Test setValues method
     *
     * @return void
     */
    public function testSetValues(): void
    {
        $values1 = [2, 3];
        $addRule = new AddRule($values1);
        $this->assertSame($values1, $addRule->getValues());

        $values2 = [4, 5, 6];
        $addRule->setValues($values2);
        $this->assertSame($values2, $addRule->getValues());
    }

    /**
     * Test addValue method
     *
     * @return void
     */
    public function testAddValue(): void
    {
        $addRule = new AddRule([2, 3]);
        $this->assertCount(2, $addRule->getValues());

        $addRule->addValue(4);
        $this->assertCount(3, $addRule->getValues());
        $this->assertSame(4, $addRule->getValues()[2]);

        $varRule = new VarRule('extra');
        $addRule->addValue($varRule);
        $this->assertCount(4, $addRule->getValues());
        $this->assertSame($varRule, $addRule->getValues()[3]);
    }

    /**
     * Test toArray method
     *
     * @return void
     */
    public function testToArray(): void
    {
        $values = [2, 3, new VarRule('extra')];
        $addRule = new AddRule($values);

        $expected = [
            '+' => [
                2,
                3,
                ['var' => 'extra'],
            ],
        ];

        $this->assertEquals($expected, $addRule->toArray());
    }

    /**
     * Test with empty values array
     *
     * @return void
     */
    public function testEmptyValues(): void
    {
        $addRule = new AddRule([]);

        $expected = [
            '+' => [],
        ];

        $this->assertEquals($expected, $addRule->toArray());
    }

    /**
     * Test with a single value (unary plus)
     *
     * @return void
     */
    public function testSingleValue(): void
    {
        $addRule = new AddRule([5]);

        $expected = [
            '+' => [5],
        ];

        $this->assertEquals($expected, $addRule->toArray());
    }

    /**
     * Test fluid interface
     *
     * @return void
     */
    public function testFluidInterface(): void
    {
        $addRule = new AddRule([]);

        $returnedRule = $addRule
            ->setValues([2, 3])
            ->addValue(4);

        $this->assertSame($addRule, $returnedRule);
        $this->assertEquals([2, 3, 4], $addRule->getValues());
    }

    /**
     * Test factory methods
     *
     * @return void
     */
    public function testFactoryMethods(): void
    {
        $rule = JsonLogicRuleFactory::add([
            JsonLogicRuleFactory::var('a'),
            JsonLogicRuleFactory::var('b'),
            5,
        ]);

        $expected = [
            '+' => [
                ['var' => 'a'],
                ['var' => 'b'],
                5,
            ],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }
}
