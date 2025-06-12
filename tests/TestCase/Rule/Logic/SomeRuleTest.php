<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Rule\Logic;

use PHPUnit\Framework\TestCase;
use RuleFlow\Rule\Comparison\EqualsRule;
use RuleFlow\Rule\Comparison\GreaterThanRule;
use RuleFlow\Rule\JsonLogicRuleFactory;
use RuleFlow\Rule\Logic\SomeRule;
use RuleFlow\Rule\Variable\VarRule;

/**
 * SomeRule Test
 */
class SomeRuleTest extends TestCase
{
    /**
     * Test constructor and getters
     *
     * @return void
     */
    public function testConstructorAndGetters(): void
    {
        $collection = [-1, 0, 1];
        $condition = new GreaterThanRule(new VarRule(''), 0);

        $someRule = new SomeRule($collection, $condition);

        $this->assertEquals('some', $someRule->getOperator());
        $this->assertSame($collection, $someRule->getCollection());
        $this->assertSame($condition, $someRule->getCondition());
    }

    /**
     * Test toArray method with simple test
     *
     * @return void
     */
    public function testToArraySimple(): void
    {
        // Example from JsonLogic docs: {"some":[[-1,0,1], {">": [{"var":""}, 0]}]}
        $collection = [-1, 0, 1];
        $condition = new GreaterThanRule(new VarRule(''), 0);

        $someRule = new SomeRule($collection, $condition);

        $expected = [
            'some' => [
                [-1, 0, 1],
                [
                    '>' => [
                        ['var' => ''],
                        0,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $someRule->toArray());
    }

    /**
     * Test with variable collection
     *
     * @return void
     */
    public function testWithVariableCollection(): void
    {
        // Test if some numbers in variable array are positive
        $collection = new VarRule('numbers');
        $condition = new GreaterThanRule(new VarRule(''), 0);

        $someRule = new SomeRule($collection, $condition);

        $expected = [
            'some' => [
                ['var' => 'numbers'],
                [
                    '>' => [
                        ['var' => ''],
                        0,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $someRule->toArray());
    }

    /**
     * Test pie example from docs
     *
     * @return void
     */
    public function testPieExample(): void
    {
        // Example from JsonLogic docs: Check if any pie has apple filling
        $collection = new VarRule('pies');
        $condition = new EqualsRule(new VarRule('filling'), 'apple');

        $someRule = new SomeRule($collection, $condition);

        $expected = [
            'some' => [
                ['var' => 'pies'],
                [
                    '==' => [
                        ['var' => 'filling'],
                        'apple',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $someRule->toArray());
    }

    /**
     * Test with complex condition
     *
     * @return void
     */
    public function testWithComplexCondition(): void
    {
        // Test if some users have admin privileges and are active
        $collection = new VarRule('users');
        $activeCondition = new EqualsRule(new VarRule('status'), 'active');
        $roleCondition = new EqualsRule(new VarRule('role'), 'admin');
        $andRule = JsonLogicRuleFactory::and([$activeCondition, $roleCondition]);

        $someRule = new SomeRule($collection, $andRule);

        $expected = [
            'some' => [
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

        $this->assertEquals($expected, $someRule->toArray());
    }

    /**
     * Test using factory methods
     *
     * @return void
     */
    public function testFactoryMethods(): void
    {
        $rule = JsonLogicRuleFactory::some(
            JsonLogicRuleFactory::var('transactions'),
            JsonLogicRuleFactory::greaterThan(
                JsonLogicRuleFactory::var('amount'),
                1000,
            ),
        );

        $expected = [
            'some' => [
                ['var' => 'transactions'],
                [
                    '>' => [
                        ['var' => 'amount'],
                        1000,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }
}
