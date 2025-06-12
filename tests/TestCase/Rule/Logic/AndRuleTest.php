<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Rule\Logic;

use PHPUnit\Framework\TestCase;
use RuleFlow\Rule\Comparison\EqualsRule;
use RuleFlow\Rule\Comparison\GreaterThanRule;
use RuleFlow\Rule\Comparison\LessThanRule;
use RuleFlow\Rule\JsonLogicRuleFactory;
use RuleFlow\Rule\Logic\AndRule;
use RuleFlow\Rule\Variable\VarRule;

/**
 * AndRule Test
 */
class AndRuleTest extends TestCase
{
    /**
     * Test constructor and getters
     *
     * @return void
     */
    public function testConstructorAndGetters(): void
    {
        $rules = [
            new EqualsRule(new VarRule('a'), 1),
            new EqualsRule(new VarRule('b'), 2),
        ];

        $andRule = new AndRule($rules);

        $this->assertEquals('and', $andRule->getOperator());
        $this->assertSame($rules, $andRule->getRules());
    }

    /**
     * Test addRule method
     *
     * @return void
     */
    public function testAddRule(): void
    {
        $rule1 = new EqualsRule(new VarRule('a'), 1);
        $andRule = new AndRule([$rule1]);
        $this->assertCount(1, $andRule->getRules());

        $rule2 = new EqualsRule(new VarRule('b'), 2);
        $andRule->addRule($rule2);
        $this->assertCount(2, $andRule->getRules());
        $this->assertSame($rule2, $andRule->getRules()[1]);

        $rule3 = new EqualsRule(new VarRule('c'), 3);
        $andRule->addRule($rule3);
        $this->assertCount(3, $andRule->getRules());
        $this->assertSame($rule3, $andRule->getRules()[2]);
    }

    /**
     * Test toArray method with simple rules
     *
     * @return void
     */
    public function testToArraySimple(): void
    {
        // Example from JsonLogic docs: {"and":[true, true]}
        $andRule = new AndRule([true, true]);

        $expected = [
            'and' => [true, true],
        ];

        $this->assertEquals($expected, $andRule->toArray());
    }

    /**
     * Test with more complex rules
     *
     * @return void
     */
    public function testWithComplexRules(): void
    {
        // Example combining several conditions
        $ageCheck = new GreaterThanRule(new VarRule('age'), 18);
        $memberCheck = new EqualsRule(new VarRule('isMember'), true);

        $andRule = new AndRule([$ageCheck, $memberCheck]);

        $expected = [
            'and' => [
                [
                    '>' => [
                        ['var' => 'age'],
                        18,
                    ],
                ],
                [
                    '==' => [
                        ['var' => 'isMember'],
                        true,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $andRule->toArray());
    }

    /**
     * Test with multiple conditions from docs (between example)
     *
     * @return void
     */
    public function testBetweenExample(): void
    {
        // Example from JsonLogic docs: Between - Temperature check (18 < temp < 25)
        $lowerCheck = new GreaterThanRule(new VarRule('temp'), 18);
        $upperCheck = new LessThanRule(new VarRule('temp'), 25);

        $andRule = new AndRule([$lowerCheck, $upperCheck]);

        $expected = [
            'and' => [
                [
                    '>' => [
                        ['var' => 'temp'],
                        18,
                    ],
                ],
                [
                    '<' => [
                        ['var' => 'temp'],
                        25,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $andRule->toArray());
    }

    /**
     * Test short-circuit example
     *
     * @return void
     */
    public function testShortCircuitExample(): void
    {
        // Example from JsonLogic docs: Short-circuit example
        $andRule = new AndRule([false, 'unreachable']);

        $expected = [
            'and' => [false, 'unreachable'],
        ];

        $this->assertEquals($expected, $andRule->toArray());
    }

    /**
     * Test fluid interface
     *
     * @return void
     */
    public function testFluidInterface(): void
    {
        $andRule = new AndRule([]);

        $rule1 = new EqualsRule(new VarRule('a'), 1);
        $rule2 = new EqualsRule(new VarRule('b'), 2);

        $returnedRule = $andRule
            ->addRule($rule1)
            ->addRule($rule2);

        $this->assertSame($andRule, $returnedRule);
        $expectedRules = [$rule1, $rule2];
        $this->assertEquals($expectedRules, $andRule->getRules());
    }

    /**
     * Test using factory methods
     *
     * @return void
     */
    public function testFactoryMethods(): void
    {
        $rule = JsonLogicRuleFactory::and([
            JsonLogicRuleFactory::greaterThan(
                JsonLogicRuleFactory::var('age'),
                21,
            ),
            JsonLogicRuleFactory::equals(
                JsonLogicRuleFactory::var('hasID'),
                true,
            ),
        ]);

        $expected = [
            'and' => [
                [
                    '>' => [
                        ['var' => 'age'],
                        21,
                    ],
                ],
                [
                    '==' => [
                        ['var' => 'hasID'],
                        true,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }
}
