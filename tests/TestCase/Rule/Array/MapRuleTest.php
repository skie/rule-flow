<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Rule\Array;

use PHPUnit\Framework\TestCase;
use RuleFlow\Rule\Array\MapRule;
use RuleFlow\Rule\Comparison\GreaterThanOrEqualRule;
use RuleFlow\Rule\Conditional\IfRule;
use RuleFlow\Rule\JsonLogicRuleFactory;
use RuleFlow\Rule\Math\MultiplyRule;
use RuleFlow\Rule\Variable\VarRule;

/**
 * MapRule Test
 */
class MapRuleTest extends TestCase
{
    /**
     * Test constructor and getters
     *
     * @return void
     */
    public function testConstructorAndGetters(): void
    {
        $array = new VarRule('integers');
        $transform = new MultiplyRule([new VarRule(''), 2]);

        $mapRule = new MapRule($array, $transform);

        $this->assertEquals('map', $mapRule->getOperator());
        $this->assertSame($array, $mapRule->getArray());
        $this->assertSame($transform, $mapRule->getTransform());
    }

    /**
     * Test toArray method with simple multiplication example from docs
     *
     * @return void
     */
    public function testToArraySimple(): void
    {
        // Example from JsonLogic docs: Map integers array, doubling each value
        $array = new VarRule('integers');
        $transform = new MultiplyRule([new VarRule(''), 2]);

        $mapRule = new MapRule($array, $transform);

        $expected = [
            'map' => [
                ['var' => 'integers'],
                [
                    '*' => [
                        ['var' => ''],
                        2,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $mapRule->toArray());
    }

    /**
     * Test map with complex transformation - extract property from objects
     *
     * @return void
     */
    public function testMapWithPropertyExtraction(): void
    {
        // Map users array to extract just their names
        $array = new VarRule('users');
        $transform = new VarRule('name');

        $mapRule = new MapRule($array, $transform);

        $expected = [
            'map' => [
                ['var' => 'users'],
                ['var' => 'name'],
            ],
        ];

        $this->assertEquals($expected, $mapRule->toArray());
    }

    /**
     * Test map with a conditional transformation
     *
     * @return void
     */
    public function testMapWithConditionalTransform(): void
    {
        // Map scores array, marking each as 'pass' or 'fail'
        $array = new VarRule('scores');
        $condition = new GreaterThanOrEqualRule(new VarRule(''), 60);
        $transform = new IfRule([$condition, 'pass', 'fail']);

        $mapRule = new MapRule($array, $transform);

        $expected = [
            'map' => [
                ['var' => 'scores'],
                [
                    'if' => [
                        [
                            '>=' => [
                                ['var' => ''],
                                60,
                            ],
                        ],
                        'pass',
                        'fail',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $mapRule->toArray());
    }

    /**
     * Test with a complex transformation that does calculations on object properties
     *
     * @return void
     */
    public function testComplexObjectTransformation(): void
    {
        // Map products to calculate total price (price * quantity)
        $array = new VarRule('products');
        $transform = new MultiplyRule([
            new VarRule('price'),
            new VarRule('quantity'),
        ]);

        $mapRule = new MapRule($array, $transform);

        $expected = [
            'map' => [
                ['var' => 'products'],
                [
                    '*' => [
                        ['var' => 'price'],
                        ['var' => 'quantity'],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $mapRule->toArray());
    }

    /**
     * Test using factory methods
     *
     * @return void
     */
    public function testFactoryMethods(): void
    {
        $rule = JsonLogicRuleFactory::map(
            JsonLogicRuleFactory::var('integers'),
            JsonLogicRuleFactory::multiply([
                JsonLogicRuleFactory::var(''),
                2,
            ]),
        );

        $expected = [
            'map' => [
                ['var' => 'integers'],
                [
                    '*' => [
                        ['var' => ''],
                        2,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }
}
