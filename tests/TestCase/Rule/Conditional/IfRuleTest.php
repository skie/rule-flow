<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Rule\Conditional;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuleFlow\Rule\Comparison\EqualsRule;
use RuleFlow\Rule\Comparison\GreaterThanRule;
use RuleFlow\Rule\Conditional\IfRule;
use RuleFlow\Rule\JsonLogicRuleFactory;
use RuleFlow\Rule\Logic\AndRule;
use RuleFlow\Rule\Variable\VarRule;

/**
 * IfRule Test
 */
class IfRuleTest extends TestCase
{
    /**
     * Test constructor and getters
     *
     * @return void
     */
    public function testConstructorAndGetters(): void
    {
        $branches = [true, 'yes', 'no'];
        $ifRule = new IfRule($branches);

        $this->assertEquals('if', $ifRule->getOperator());
        $this->assertSame($branches, $ifRule->getBranches());
    }

    /**
     * Test addBranch method
     *
     * @return void
     */
    public function testAddBranch(): void
    {
        $ifRule = new IfRule([true, 'first']);
        $this->assertCount(2, $ifRule->getBranches());

        $ifRule->addBranch(false, 'second', 'third');
        $this->assertCount(5, $ifRule->getBranches());
        $this->assertSame(false, $ifRule->getBranches()[2]);
        $this->assertSame('second', $ifRule->getBranches()[3]);
        $this->assertSame('third', $ifRule->getBranches()[4]);
    }

    /**
     * Test addElse method
     *
     * @return void
     */
    public function testAddElse(): void
    {
        $ifRule = new IfRule([true, 'first']);
        $this->assertCount(2, $ifRule->getBranches());

        $ifRule->addElse('fallback');
        $this->assertCount(3, $ifRule->getBranches());
        $this->assertSame('fallback', $ifRule->getBranches()[2]);
    }

    /**
     * Test toArray method with simple example
     *
     * @return void
     */
    public function testToArraySimple(): void
    {
        // Example from JsonLogic docs: {"if" : [true, "yes", "no"]}
        $ifRule = new IfRule([true, 'yes', 'no']);

        $expected = [
            'if' => [true, 'yes', 'no'],
        ];

        $this->assertEquals($expected, $ifRule->toArray());
    }

    /**
     * Test with condition as rule
     *
     * @return void
     */
    public function testWithRuleCondition(): void
    {
        // Example with rule as condition
        $condition = new GreaterThanRule(new VarRule('age'), 21);
        $ifRule = new IfRule([$condition, 'adult', 'minor']);

        $expected = [
            'if' => [
                [
                    '>' => [
                        ['var' => 'age'],
                        21,
                    ],
                ],
                'adult',
                'minor',
            ],
        ];

        $this->assertEquals($expected, $ifRule->toArray());
    }

    /**
     * Test ternary style with complex condition
     *
     * @return void
     */
    public function testTernaryWithComplexCondition(): void
    {
        // Example: If user is active and has admin role, show admin panel, else show user panel
        $activeCheck = new EqualsRule(new VarRule('user.status'), 'active');
        $roleCheck = new EqualsRule(new VarRule('user.role'), 'admin');
        $condition = new AndRule([$activeCheck, $roleCheck]);

        $ifRule = new IfRule([$condition, 'Show Admin Panel', 'Show User Panel']);

        $expected = [
            'if' => [
                [
                    'and' => [
                        [
                            '==' => [
                                ['var' => 'user.status'],
                                'active',
                            ],
                        ],
                        [
                            '==' => [
                                ['var' => 'user.role'],
                                'admin',
                            ],
                        ],
                    ],
                ],
                'Show Admin Panel',
                'Show User Panel',
            ],
        ];

        $this->assertEquals($expected, $ifRule->toArray());
    }

    /**
     * Test if-elsif-else pattern
     *
     * @return void
     */
    public function testIfElsifElsePattern(): void
    {
        // Example from JsonLogic docs: if-elsif-else pattern
        $ifRule = new IfRule([
            new EqualsRule(new VarRule('temp'), 0),
            'freezing',
            new EqualsRule(new VarRule('temp'), 100),
            'boiling',
            'comfortable',
        ]);

        $expected = [
            'if' => [
                [
                    '==' => [
                        ['var' => 'temp'],
                        0,
                    ],
                ],
                'freezing',
                [
                    '==' => [
                        ['var' => 'temp'],
                        100,
                    ],
                ],
                'boiling',
                'comfortable',
            ],
        ];

        $this->assertEquals($expected, $ifRule->toArray());
    }

    /**
     * Test age categories example
     *
     * @return void
     */
    public function testAgeCategoriesExample(): void
    {
        // Example for age categories: child, teen, adult, senior
        $ifRule = new IfRule([
            new GreaterThanRule(new VarRule('age'), 65),
            'senior',
            new GreaterThanRule(new VarRule('age'), 18),
            'adult',
            new GreaterThanRule(new VarRule('age'), 12),
            'teen',
            'child',
        ]);

        $expected = [
            'if' => [
                [
                    '>' => [
                        ['var' => 'age'],
                        65,
                    ],
                ],
                'senior',
                [
                    '>' => [
                        ['var' => 'age'],
                        18,
                    ],
                ],
                'adult',
                [
                    '>' => [
                        ['var' => 'age'],
                        12,
                    ],
                ],
                'teen',
                'child',
            ],
        ];

        $this->assertEquals($expected, $ifRule->toArray());
    }

    /**
     * Test invalid constructor arguments
     *
     * @return void
     */
    public function testInvalidConstructorArguments(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('If rule must have at least a condition and result');

        new IfRule([new EqualsRule(new VarRule('a'), 5)]);
    }

    /**
     * Test toArray method with multiple conditions
     *
     * @return void
     */
    public function testToArrayMultipleConditions(): void
    {
        $condition1 = new EqualsRule(new VarRule('a'), 5);
        $result1 = 'equals 5';

        $condition2 = new EqualsRule(new VarRule('a'), 10);
        $result2 = 'equals 10';

        $elseResult = 'something else';

        $ifRule = new IfRule([$condition1, $result1, $condition2, $result2, $elseResult]);

        $expected = [
            'if' => [
                [
                    '==' => [
                        ['var' => 'a'],
                        5,
                    ],
                ],
                'equals 5',
                [
                    '==' => [
                        ['var' => 'a'],
                        10,
                    ],
                ],
                'equals 10',
                'something else',
            ],
        ];

        $this->assertEquals($expected, $ifRule->toArray());
    }

    /**
     * Test building if-then-else using fluent interface
     *
     * @return void
     */
    public function testFluentInterface(): void
    {
        $condition1 = new EqualsRule(new VarRule('score'), 100);
        $result1 = 'perfect';

        $condition2 = new EqualsRule(new VarRule('score'), 0);
        $result2 = 'zero';

        $elseResult = 'somewhere in between';

        $ifRule = new IfRule([$condition1, $result1]);
        $ifRule->addBranch($condition2, $result2)
            ->addElse($elseResult);

        $expected = [
            'if' => [
                [
                    '==' => [
                        ['var' => 'score'],
                        100,
                    ],
                ],
                'perfect',
                [
                    '==' => [
                        ['var' => 'score'],
                        0,
                    ],
                ],
                'zero',
                'somewhere in between',
            ],
        ];

        $this->assertEquals($expected, $ifRule->toArray());
    }

    /**
     * Test using factory methods
     *
     * @return void
     */
    public function testFactoryMethods(): void
    {
        $rule = JsonLogicRuleFactory::if([
            JsonLogicRuleFactory::greaterThan(
                JsonLogicRuleFactory::var('score'),
                60,
            ),
            'Pass',
            'Fail',
        ]);

        $expected = [
            'if' => [
                [
                    '>' => [
                        ['var' => 'score'],
                        60,
                    ],
                ],
                'Pass',
                'Fail',
            ],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }
}
