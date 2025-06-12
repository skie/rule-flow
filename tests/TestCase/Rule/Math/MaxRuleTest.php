<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Rule\Math;

use PHPUnit\Framework\TestCase;
use RuleFlow\Rule\JsonLogicRuleFactory;
use RuleFlow\Rule\Math\MaxRule;
use RuleFlow\Rule\Variable\VarRule;

/**
 * MaxRule Test
 */
class MaxRuleTest extends TestCase
{
    /**
     * Test constructor and getters
     *
     * @return void
     */
    public function testConstructorAndGetters(): void
    {
        $values = [1, 5, 3];

        $maxRule = new MaxRule($values);

        $this->assertEquals('max', $maxRule->getOperator());
        $this->assertSame($values, $maxRule->getValues());
    }

    /**
     * Test setValues method
     *
     * @return void
     */
    public function testSetValues(): void
    {
        $values1 = [1, 2, 3];
        $maxRule = new MaxRule($values1);
        $this->assertSame($values1, $maxRule->getValues());

        $values2 = [4, 5, 6];
        $maxRule->setValues($values2);
        $this->assertSame($values2, $maxRule->getValues());
    }

    /**
     * Test addValue method
     *
     * @return void
     */
    public function testAddValue(): void
    {
        $maxRule = new MaxRule([1, 2]);
        $this->assertCount(2, $maxRule->getValues());

        $maxRule->addValue(3);
        $this->assertCount(3, $maxRule->getValues());
        $this->assertSame(3, $maxRule->getValues()[2]);

        $maxRule->addValue(4);
        $this->assertCount(4, $maxRule->getValues());
        $this->assertSame(4, $maxRule->getValues()[3]);
    }

    /**
     * Test toArray method with simple values
     *
     * @return void
     */
    public function testToArraySimple(): void
    {
        // Example: {"max": [1, 2, 3]}
        $maxRule = new MaxRule([1, 2, 3]);

        $expected = [
            'max' => [1, 2, 3],
        ];

        $this->assertEquals($expected, $maxRule->toArray());
    }

    /**
     * Test with variables in values
     *
     * @return void
     */
    public function testWithVariables(): void
    {
        // Example: Get maximum of user score and threshold
        $maxRule = new MaxRule([
            new VarRule('user.score'),
            new VarRule('threshold'),
        ]);

        $expected = [
            'max' => [
                ['var' => 'user.score'],
                ['var' => 'threshold'],
            ],
        ];

        $this->assertEquals($expected, $maxRule->toArray());
    }

    /**
     * Test with mixed values
     *
     * @return void
     */
    public function testWithMixedValues(): void
    {
        // Example: Get maximum of user score and fixed value
        $maxRule = new MaxRule([
            new VarRule('user.score'),
            100,
        ]);

        $expected = [
            'max' => [
                ['var' => 'user.score'],
                100,
            ],
        ];

        $this->assertEquals($expected, $maxRule->toArray());
    }

    /**
     * Test using factory methods
     *
     * @return void
     */
    public function testFactoryMethods(): void
    {
        $rule = JsonLogicRuleFactory::max([
            JsonLogicRuleFactory::var('score1'),
            JsonLogicRuleFactory::var('score2'),
            JsonLogicRuleFactory::var('score3'),
        ]);

        $expected = [
            'max' => [
                ['var' => 'score1'],
                ['var' => 'score2'],
                ['var' => 'score3'],
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
        $maxRule = new MaxRule([1, 2]);

        $returnedRule = $maxRule
            ->addValue(3)
            ->setValues([4, 5, 6]);

        $this->assertSame($maxRule, $returnedRule);
        $this->assertEquals([4, 5, 6], $maxRule->getValues());
    }

    /**
     * Test high score scenario
     *
     * @return void
     */
    public function testHighScoreScenario(): void
    {
        // Example: Get high score between user's current score and previous high score
        $maxRule = new MaxRule([
            new VarRule('user.currentScore'),
            new VarRule('user.highScore'),
        ]);

        $expected = [
            'max' => [
                ['var' => 'user.currentScore'],
                ['var' => 'user.highScore'],
            ],
        ];

        $this->assertEquals($expected, $maxRule->toArray());
    }
}
