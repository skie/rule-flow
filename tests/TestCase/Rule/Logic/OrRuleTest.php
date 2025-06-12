<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Rule\Logic;

use PHPUnit\Framework\TestCase;
use RuleFlow\Rule\Comparison\EqualsRule;
use RuleFlow\Rule\JsonLogicRuleFactory;
use RuleFlow\Rule\Logic\OrRule;
use RuleFlow\Rule\Variable\VarRule;

/**
 * OrRule Test
 */
class OrRuleTest extends TestCase
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

        $orRule = new OrRule($rules);

        $this->assertEquals('or', $orRule->getOperator());
        $this->assertSame($rules, $orRule->getRules());
    }

    /**
     * Test addRule method
     *
     * @return void
     */
    public function testAddRule(): void
    {
        $rule1 = new EqualsRule(new VarRule('a'), 1);
        $orRule = new OrRule([$rule1]);
        $this->assertCount(1, $orRule->getRules());

        $rule2 = new EqualsRule(new VarRule('b'), 2);
        $orRule->addRule($rule2);
        $this->assertCount(2, $orRule->getRules());
        $this->assertSame($rule2, $orRule->getRules()[1]);

        $rule3 = new EqualsRule(new VarRule('c'), 3);
        $orRule->addRule($rule3);
        $this->assertCount(3, $orRule->getRules());
        $this->assertSame($rule3, $orRule->getRules()[2]);
    }

    /**
     * Test toArray method with simple rules
     *
     * @return void
     */
    public function testToArraySimple(): void
    {
        // Example from JsonLogic docs: {"or":[false, true]}
        $orRule = new OrRule([false, true]);

        $expected = [
            'or' => [false, true],
        ];

        $this->assertEquals($expected, $orRule->toArray());
    }

    /**
     * Test with more complex rules
     *
     * @return void
     */
    public function testWithComplexRules(): void
    {
        // Example combining several conditions
        $adminCheck = new EqualsRule(new VarRule('role'), 'admin');
        $managerCheck = new EqualsRule(new VarRule('role'), 'manager');

        $orRule = new OrRule([$adminCheck, $managerCheck]);

        $expected = [
            'or' => [
                [
                    '==' => [
                        ['var' => 'role'],
                        'admin',
                    ],
                ],
                [
                    '==' => [
                        ['var' => 'role'],
                        'manager',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $orRule->toArray());
    }

    /**
     * Test with permission check example
     *
     * @return void
     */
    public function testPermissionCheckExample(): void
    {
        // Example for user permission check (is admin OR is content owner)
        $adminCheck = new EqualsRule(new VarRule('user.role'), 'admin');
        $ownerCheck = new EqualsRule(new VarRule('user.id'), new VarRule('content.owner_id'));

        $orRule = new OrRule([$adminCheck, $ownerCheck]);

        $expected = [
            'or' => [
                [
                    '==' => [
                        ['var' => 'user.role'],
                        'admin',
                    ],
                ],
                [
                    '==' => [
                        ['var' => 'user.id'],
                        ['var' => 'content.owner_id'],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $orRule->toArray());
    }

    /**
     * Test short-circuit example
     *
     * @return void
     */
    public function testShortCircuitExample(): void
    {
        // Example from JsonLogic docs: Short-circuit example
        $orRule = new OrRule([true, 'unreachable']);

        $expected = [
            'or' => [true, 'unreachable'],
        ];

        $this->assertEquals($expected, $orRule->toArray());
    }

    /**
     * Test as default value example
     *
     * @return void
     */
    public function testDefaultValueExample(): void
    {
        // Example from JsonLogic docs: Using OR to provide a default value
        $orRule = new OrRule([new VarRule('user.name'), 'Anonymous']);

        $expected = [
            'or' => [
                ['var' => 'user.name'],
                'Anonymous',
            ],
        ];

        $this->assertEquals($expected, $orRule->toArray());
    }

    /**
     * Test fluid interface
     *
     * @return void
     */
    public function testFluidInterface(): void
    {
        $orRule = new OrRule([]);

        $rule1 = new EqualsRule(new VarRule('a'), 1);
        $rule2 = new EqualsRule(new VarRule('b'), 2);

        $returnedRule = $orRule
            ->addRule($rule1)
            ->addRule($rule2);

        $this->assertSame($orRule, $returnedRule);
        $expectedRules = [$rule1, $rule2];
        $this->assertEquals($expectedRules, $orRule->getRules());
    }

    /**
     * Test using factory methods
     *
     * @return void
     */
    public function testFactoryMethods(): void
    {
        $rule = JsonLogicRuleFactory::or([
            JsonLogicRuleFactory::greaterThan(
                JsonLogicRuleFactory::var('age'),
                65,
            ),
            JsonLogicRuleFactory::equals(
                JsonLogicRuleFactory::var('hasDiscount'),
                true,
            ),
        ]);

        $expected = [
            'or' => [
                [
                    '>' => [
                        ['var' => 'age'],
                        65,
                    ],
                ],
                [
                    '==' => [
                        ['var' => 'hasDiscount'],
                        true,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }
}
