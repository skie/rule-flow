<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Rule\Math;

use PHPUnit\Framework\TestCase;
use RuleFlow\Rule\JsonLogicRuleFactory;
use RuleFlow\Rule\Math\MinRule;
use RuleFlow\Rule\Variable\VarRule;

/**
 * MinRule Test
 */
class MinRuleTest extends TestCase
{
    /**
     * Test constructor and getters
     *
     * @return void
     */
    public function testConstructorAndGetters(): void
    {
        $values = [5, 1, 3];

        $minRule = new MinRule($values);

        $this->assertEquals('min', $minRule->getOperator());
        $this->assertSame($values, $minRule->getValues());
    }

    /**
     * Test setValues method
     *
     * @return void
     */
    public function testSetValues(): void
    {
        $values1 = [1, 2, 3];
        $minRule = new MinRule($values1);
        $this->assertSame($values1, $minRule->getValues());

        $values2 = [4, 5, 6];
        $minRule->setValues($values2);
        $this->assertSame($values2, $minRule->getValues());
    }

    /**
     * Test addValue method
     *
     * @return void
     */
    public function testAddValue(): void
    {
        $minRule = new MinRule([3, 2]);
        $this->assertCount(2, $minRule->getValues());

        $minRule->addValue(1);
        $this->assertCount(3, $minRule->getValues());
        $this->assertSame(1, $minRule->getValues()[2]);

        $minRule->addValue(0);
        $this->assertCount(4, $minRule->getValues());
        $this->assertSame(0, $minRule->getValues()[3]);
    }

    /**
     * Test toArray method with simple values
     *
     * @return void
     */
    public function testToArraySimple(): void
    {
        // Example: {"min": [3, 1, 2]}
        $minRule = new MinRule([3, 1, 2]);

        $expected = [
            'min' => [3, 1, 2],
        ];

        $this->assertEquals($expected, $minRule->toArray());
    }

    /**
     * Test with variables in values
     *
     * @return void
     */
    public function testWithVariables(): void
    {
        // Example: Get minimum of cart total and budget limit
        $minRule = new MinRule([
            new VarRule('cart.total'),
            new VarRule('user.budget'),
        ]);

        $expected = [
            'min' => [
                ['var' => 'cart.total'],
                ['var' => 'user.budget'],
            ],
        ];

        $this->assertEquals($expected, $minRule->toArray());
    }

    /**
     * Test with mixed values
     *
     * @return void
     */
    public function testWithMixedValues(): void
    {
        // Example: Get minimum of user discount and maximum allowed discount
        $minRule = new MinRule([
            new VarRule('user.discount'),
            25, // Maximum discount percentage
        ]);

        $expected = [
            'min' => [
                ['var' => 'user.discount'],
                25,
            ],
        ];

        $this->assertEquals($expected, $minRule->toArray());
    }

    /**
     * Test using factory methods
     *
     * @return void
     */
    public function testFactoryMethods(): void
    {
        $rule = JsonLogicRuleFactory::min([
            JsonLogicRuleFactory::var('price1'),
            JsonLogicRuleFactory::var('price2'),
            JsonLogicRuleFactory::var('price3'),
        ]);

        $expected = [
            'min' => [
                ['var' => 'price1'],
                ['var' => 'price2'],
                ['var' => 'price3'],
            ],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }

    /**
     * Test fluid interface
     *
     * @return void
     */
    public function testFluidInterface(): void
    {
        $minRule = new MinRule([3, 2]);

        $returnedRule = $minRule
            ->addValue(1)
            ->setValues([0, 2, 1]);

        $this->assertSame($minRule, $returnedRule);
        $this->assertEquals([0, 2, 1], $minRule->getValues());
    }

    /**
     * Test capping value scenario
     *
     * @return void
     */
    public function testCappingValueScenario(): void
    {
        // Example: Cap discount to maximum allowed value
        $minRule = new MinRule([
            new VarRule('request.discount'),
            new VarRule('settings.maxDiscount'),
        ]);

        $expected = [
            'min' => [
                ['var' => 'request.discount'],
                ['var' => 'settings.maxDiscount'],
            ],
        ];

        $this->assertEquals($expected, $minRule->toArray());
    }
}
