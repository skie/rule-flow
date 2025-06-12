<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Rule\Array;

use PHPUnit\Framework\TestCase;
use RuleFlow\Rule\Array\ReduceRule;
use RuleFlow\Rule\JsonLogicRuleFactory;
use RuleFlow\Rule\Math\AddRule;
use RuleFlow\Rule\Math\MaxRule;
use RuleFlow\Rule\Math\MultiplyRule;
use RuleFlow\Rule\Variable\VarRule;

/**
 * ReduceRule Test
 */
class ReduceRuleTest extends TestCase
{
    /**
     * Test constructor and getters
     *
     * @return void
     */
    public function testConstructorAndGetters(): void
    {
        $array = new VarRule('integers');
        $reducer = new AddRule([new VarRule('current'), new VarRule('accumulator')]);
        $initial = 0;

        $reduceRule = new ReduceRule($array, $reducer, $initial);

        $this->assertEquals('reduce', $reduceRule->getOperator());
        $this->assertSame($array, $reduceRule->getArray());
        $this->assertSame($reducer, $reduceRule->getReducer());
        $this->assertSame($initial, $reduceRule->getInitial());
    }

    /**
     * Test toArray method with simple sum example from docs
     *
     * @return void
     */
    public function testToArraySimpleSum(): void
    {
        // Example from JsonLogic docs: Reduce integers array to a sum
        $array = new VarRule('integers');
        $reducer = new AddRule([new VarRule('current'), new VarRule('accumulator')]);
        $initial = 0;

        $reduceRule = new ReduceRule($array, $reducer, $initial);

        $expected = [
            'reduce' => [
                ['var' => 'integers'],
                [
                    '+' => [
                        ['var' => 'current'],
                        ['var' => 'accumulator'],
                    ],
                ],
                0,
            ],
        ];

        $this->assertEquals($expected, $reduceRule->toArray());
    }

    /**
     * Test reduce to find product of all numbers
     *
     * @return void
     */
    public function testReduceToProduct(): void
    {
        // Reduce integers array to their product
        $array = new VarRule('integers');
        $reducer = new MultiplyRule([new VarRule('current'), new VarRule('accumulator')]);
        $initial = 1;

        $reduceRule = new ReduceRule($array, $reducer, $initial);

        $expected = [
            'reduce' => [
                ['var' => 'integers'],
                [
                    '*' => [
                        ['var' => 'current'],
                        ['var' => 'accumulator'],
                    ],
                ],
                1,
            ],
        ];

        $this->assertEquals($expected, $reduceRule->toArray());
    }

    /**
     * Test reduce to find the maximum value
     *
     * @return void
     */
    public function testReduceToFindMax(): void
    {
        // Reduce to find the maximum value
        $array = new VarRule('integers');
        $reducer = new MaxRule([new VarRule('current'), new VarRule('accumulator')]);
        $initial = 0;

        $reduceRule = new ReduceRule($array, $reducer, $initial);

        $expected = [
            'reduce' => [
                ['var' => 'integers'],
                [
                    'max' => [
                        ['var' => 'current'],
                        ['var' => 'accumulator'],
                    ],
                ],
                0,
            ],
        ];

        $this->assertEquals($expected, $reduceRule->toArray());
    }

    /**
     * Test reduce to concatenate strings
     *
     * @return void
     */
    public function testReduceToJoinStrings(): void
    {
        // Reduce array of words to a sentence
        $array = new VarRule('words');
        $reducer = new AddRule([new VarRule('accumulator'), ' ', new VarRule('current')]);
        $initial = '';

        $reduceRule = new ReduceRule($array, $reducer, $initial);

        $expected = [
            'reduce' => [
                ['var' => 'words'],
                [
                    '+' => [
                        ['var' => 'accumulator'],
                        ' ',
                        ['var' => 'current'],
                    ],
                ],
                '',
            ],
        ];

        $this->assertEquals($expected, $reduceRule->toArray());
    }

    /**
     * Test reduce to calculate total from objects
     *
     * @return void
     */
    public function testReduceCalculateTotal(): void
    {
        // Reduce to calculate total cost from cart items
        $array = new VarRule('cart');
        $currentTotal = new MultiplyRule([
            new VarRule('current.price'),
            new VarRule('current.quantity'),
        ]);
        $reducer = new AddRule([
            $currentTotal,
            new VarRule('accumulator'),
        ]);
        $initial = 0;

        $reduceRule = new ReduceRule($array, $reducer, $initial);

        $expected = [
            'reduce' => [
                ['var' => 'cart'],
                [
                    '+' => [
                        [
                            '*' => [
                                ['var' => 'current.price'],
                                ['var' => 'current.quantity'],
                            ],
                        ],
                        ['var' => 'accumulator'],
                    ],
                ],
                0,
            ],
        ];

        $this->assertEquals($expected, $reduceRule->toArray());
    }

    /**
     * Test using factory methods
     *
     * @return void
     */
    public function testFactoryMethods(): void
    {
        $rule = JsonLogicRuleFactory::reduce(
            JsonLogicRuleFactory::var('integers'),
            JsonLogicRuleFactory::add([
                JsonLogicRuleFactory::var('current'),
                JsonLogicRuleFactory::var('accumulator'),
            ]),
            0,
        );

        $expected = [
            'reduce' => [
                ['var' => 'integers'],
                [
                    '+' => [
                        ['var' => 'current'],
                        ['var' => 'accumulator'],
                    ],
                ],
                0,
            ],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }
}
