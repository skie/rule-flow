<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Rule\Array;

use PHPUnit\Framework\TestCase;
use RuleFlow\Rule\Array\FilterRule;
use RuleFlow\Rule\Comparison\EqualsRule;
use RuleFlow\Rule\Comparison\GreaterThanRule;
use RuleFlow\Rule\JsonLogicRuleFactory;
use RuleFlow\Rule\Math\ModuloRule;
use RuleFlow\Rule\Variable\VarRule;

/**
 * FilterRule Test
 */
class FilterRuleTest extends TestCase
{
    /**
     * Test constructor and getters
     *
     * @return void
     */
    public function testConstructorAndGetters(): void
    {
        $array = new VarRule('integers');
        $filter = new GreaterThanRule(new VarRule(''), 0);

        $filterRule = new FilterRule($array, $filter);

        $this->assertEquals('filter', $filterRule->getOperator());
        $this->assertSame($array, $filterRule->getArray());
        $this->assertSame($filter, $filterRule->getFilter());
    }

    /**
     * Test toArray method with simple filter
     *
     * @return void
     */
    public function testToArraySimple(): void
    {
        $array = new VarRule('integers');
        $filter = new GreaterThanRule(new VarRule(''), 0);

        $filterRule = new FilterRule($array, $filter);

        $expected = [
            'filter' => [
                ['var' => 'integers'],
                [
                    '>' => [
                        ['var' => ''],
                        0,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $filterRule->toArray());
    }

    /**
     * Test filter with modulo operation (odd numbers example)
     *
     * @return void
     */
    public function testFilterWithModulo(): void
    {
        // Example from JsonLogic docs: Filter integers to keep only odd numbers
        $array = new VarRule('integers');
        $filter = new ModuloRule(new VarRule(''), 2); // var % 2 (returns 1 for odd, 0 for even)

        $filterRule = new FilterRule($array, $filter);

        $expected = [
            'filter' => [
                ['var' => 'integers'],
                [
                    '%' => [
                        ['var' => ''],
                        2,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $filterRule->toArray());
    }

    /**
     * Test complex filter example from docs (filtering objects in array)
     *
     * @return void
     */
    public function testComplexObjectFilter(): void
    {
        // Example from JsonLogic docs: Filter pies array to find those with apple filling
        $array = new VarRule('pies');
        $filter = new EqualsRule(new VarRule('filling'), 'apple');

        $filterRule = new FilterRule($array, $filter);

        $expected = [
            'filter' => [
                ['var' => 'pies'],
                [
                    '==' => [
                        ['var' => 'filling'],
                        'apple',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $filterRule->toArray());
    }

    /**
     * Test using factory methods
     *
     * @return void
     */
    public function testFactoryMethods(): void
    {
        $rule = JsonLogicRuleFactory::filter(
            JsonLogicRuleFactory::var('integers'),
            JsonLogicRuleFactory::greaterThan(
                JsonLogicRuleFactory::var(''),
                0,
            ),
        );

        $expected = [
            'filter' => [
                ['var' => 'integers'],
                [
                    '>' => [
                        ['var' => ''],
                        0,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }
}
