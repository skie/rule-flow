<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Rule\Logic;

use PHPUnit\Framework\TestCase;
use RuleFlow\Rule\Comparison\EqualsRule;
use RuleFlow\Rule\Comparison\GreaterThanRule;
use RuleFlow\Rule\JsonLogicRuleFactory;
use RuleFlow\Rule\Logic\NoneRule;
use RuleFlow\Rule\Variable\VarRule;

/**
 * NoneRule Test
 */
class NoneRuleTest extends TestCase
{
    /**
     * Test constructor and getters
     *
     * @return void
     */
    public function testConstructorAndGetters(): void
    {
        $collection = [-3, -2, -1];
        $condition = new GreaterThanRule(new VarRule(''), 0);

        $noneRule = new NoneRule($collection, $condition);

        $this->assertEquals('none', $noneRule->getOperator());
        $this->assertSame($collection, $noneRule->getCollection());
        $this->assertSame($condition, $noneRule->getCondition());
    }

    /**
     * Test toArray method with simple test
     *
     * @return void
     */
    public function testToArraySimple(): void
    {
        // Example from JsonLogic docs: {"none":[[-3,-2,-1], {">": [{"var":""}, 0]}]}
        $collection = [-3, -2, -1];
        $condition = new GreaterThanRule(new VarRule(''), 0);

        $noneRule = new NoneRule($collection, $condition);

        $expected = [
            'none' => [
                [-3, -2, -1],
                [
                    '>' => [
                        ['var' => ''],
                        0,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $noneRule->toArray());
    }

    /**
     * Test with variable collection
     *
     * @return void
     */
    public function testWithVariableCollection(): void
    {
        // Test if none of the numbers in variable array are positive
        $collection = new VarRule('negativeNumbers');
        $condition = new GreaterThanRule(new VarRule(''), 0);

        $noneRule = new NoneRule($collection, $condition);

        $expected = [
            'none' => [
                ['var' => 'negativeNumbers'],
                [
                    '>' => [
                        ['var' => ''],
                        0,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $noneRule->toArray());
    }

    /**
     * Test with object properties
     *
     * @return void
     */
    public function testWithObjectProperties(): void
    {
        // Test if none of the products are out of stock
        $collection = new VarRule('products');
        $condition = new EqualsRule(new VarRule('stockStatus'), 'outOfStock');

        $noneRule = new NoneRule($collection, $condition);

        $expected = [
            'none' => [
                ['var' => 'products'],
                [
                    '==' => [
                        ['var' => 'stockStatus'],
                        'outOfStock',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $noneRule->toArray());
    }

    /**
     * Test with complex condition
     *
     * @return void
     */
    public function testWithComplexCondition(): void
    {
        // Test if none of the transactions are both over $1000 and flagged as suspicious
        $collection = new VarRule('transactions');
        $amountCondition = new GreaterThanRule(new VarRule('amount'), 1000);
        $flagCondition = new EqualsRule(new VarRule('flagged'), true);
        $andRule = JsonLogicRuleFactory::and([$amountCondition, $flagCondition]);

        $noneRule = new NoneRule($collection, $andRule);

        $expected = [
            'none' => [
                ['var' => 'transactions'],
                [
                    'and' => [
                        [
                            '>' => [
                                ['var' => 'amount'],
                                1000,
                            ],
                        ],
                        [
                            '==' => [
                                ['var' => 'flagged'],
                                true,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $noneRule->toArray());
    }

    /**
     * Test with empty array behavior
     *
     * @return void
     */
    public function testWithEmptyArray(): void
    {
        // Test behavior with empty array - should return true
        $collection = [];
        $condition = new GreaterThanRule(new VarRule(''), 0);

        $noneRule = new NoneRule($collection, $condition);

        $expected = [
            'none' => [
                [],
                [
                    '>' => [
                        ['var' => ''],
                        0,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $noneRule->toArray());
    }

    /**
     * Test using factory methods
     *
     * @return void
     */
    public function testFactoryMethods(): void
    {
        $rule = JsonLogicRuleFactory::none(
            JsonLogicRuleFactory::var('users'),
            JsonLogicRuleFactory::equals(
                JsonLogicRuleFactory::var('status'),
                'banned',
            ),
        );

        $expected = [
            'none' => [
                ['var' => 'users'],
                [
                    '==' => [
                        ['var' => 'status'],
                        'banned',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }
}
