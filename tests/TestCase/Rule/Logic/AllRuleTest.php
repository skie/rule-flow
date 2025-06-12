<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Rule\Logic;

use PHPUnit\Framework\TestCase;
use RuleFlow\Rule\Comparison\EqualsRule;
use RuleFlow\Rule\Comparison\GreaterThanRule;
use RuleFlow\Rule\JsonLogicRuleFactory;
use RuleFlow\Rule\Logic\AllRule;
use RuleFlow\Rule\Variable\VarRule;

/**
 * AllRule Test
 */
class AllRuleTest extends TestCase
{
    /**
     * Test constructor and getters
     *
     * @return void
     */
    public function testConstructorAndGetters(): void
    {
        $array = [1, 2, 3];
        $test = new GreaterThanRule(new VarRule(''), 0);

        $allRule = new AllRule($array, $test);

        $this->assertEquals('all', $allRule->getOperator());
        $this->assertSame($array, $allRule->getCollection());
        $this->assertSame($test, $allRule->getCondition());
    }

    /**
     * Test toArray method with simple test
     *
     * @return void
     */
    public function testToArraySimple(): void
    {
        // Example from JsonLogic docs: {"all":[[1,2,3], {">": [{"var":""}, 0]}]}
        $array = [1, 2, 3];
        $test = new GreaterThanRule(new VarRule(''), 0);

        $allRule = new AllRule($array, $test);

        $expected = [
            'all' => [
                [1, 2, 3],
                [
                    '>' => [
                        ['var' => ''],
                        0,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $allRule->toArray());
    }

    /**
     * Test with variable array
     *
     * @return void
     */
    public function testWithVariableArray(): void
    {
        // Test if all numbers in variable array are positive
        $array = new VarRule('numbers');
        $test = new GreaterThanRule(new VarRule(''), 0);

        $allRule = new AllRule($array, $test);

        $expected = [
            'all' => [
                ['var' => 'numbers'],
                [
                    '>' => [
                        ['var' => ''],
                        0,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $allRule->toArray());
    }

    /**
     * Test with object properties
     *
     * @return void
     */
    public function testWithObjectProperties(): void
    {
        // Test if all pies in array have a temperature below 100
        $array = new VarRule('pies');
        $test = new GreaterThanRule(100, new VarRule('temp'));

        $allRule = new AllRule($array, $test);

        $expected = [
            'all' => [
                ['var' => 'pies'],
                [
                    '>' => [
                        100,
                        ['var' => 'temp'],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $allRule->toArray());
    }

    /**
     * Test with complex comparison
     *
     * @return void
     */
    public function testWithComplexComparison(): void
    {
        // Test if all users in array are active and have admin role
        $array = new VarRule('users');
        $activeTest = new EqualsRule(new VarRule('status'), 'active');
        $roleTest = new EqualsRule(new VarRule('role'), 'admin');
        $andRule = JsonLogicRuleFactory::and([$activeTest, $roleTest]);

        $allRule = new AllRule($array, $andRule);

        $expected = [
            'all' => [
                ['var' => 'users'],
                [
                    'and' => [
                        [
                            '==' => [
                                ['var' => 'status'],
                                'active',
                            ],
                        ],
                        [
                            '==' => [
                                ['var' => 'role'],
                                'admin',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $allRule->toArray());
    }

    /**
     * Test using factory methods
     *
     * @return void
     */
    public function testFactoryMethods(): void
    {
        $rule = JsonLogicRuleFactory::all(
            JsonLogicRuleFactory::var('grades'),
            JsonLogicRuleFactory::greaterThanOrEqual(
                JsonLogicRuleFactory::var(''),
                60,
            ),
        );

        $expected = [
            'all' => [
                ['var' => 'grades'],
                [
                    '>=' => [
                        ['var' => ''],
                        60,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }
}
