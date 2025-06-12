<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Rule\String;

use PHPUnit\Framework\TestCase;
use RuleFlow\Rule\Conditional\IfRule;
use RuleFlow\Rule\JsonLogicRuleFactory;
use RuleFlow\Rule\String\CatRule;
use RuleFlow\Rule\Variable\VarRule;

/**
 * CatRule Test
 */
class CatRuleTest extends TestCase
{
    /**
     * Test constructor and getters
     *
     * @return void
     */
    public function testConstructorAndGetters(): void
    {
        $strings = ['Hello', ' ', 'World'];

        $catRule = new CatRule($strings);

        $this->assertEquals('cat', $catRule->getOperator());
        $this->assertSame($strings, $catRule->getStrings());
    }

    /**
     * Test setStrings method
     *
     * @return void
     */
    public function testSetStrings(): void
    {
        $strings1 = ['Hello', ' ', 'World'];
        $catRule = new CatRule($strings1);
        $this->assertSame($strings1, $catRule->getStrings());

        $strings2 = ['Goodbye', ' ', 'World'];
        $catRule->setStrings($strings2);
        $this->assertSame($strings2, $catRule->getStrings());
    }

    /**
     * Test addString method
     *
     * @return void
     */
    public function testAddString(): void
    {
        $catRule = new CatRule(['Hello']);
        $this->assertCount(1, $catRule->getStrings());

        $catRule->addString(' ');
        $this->assertCount(2, $catRule->getStrings());
        $this->assertSame(' ', $catRule->getStrings()[1]);

        $varRule = new VarRule('name');
        $catRule->addString($varRule);
        $this->assertCount(3, $catRule->getStrings());
        $this->assertSame($varRule, $catRule->getStrings()[2]);
    }

    /**
     * Test toArray method with simple strings
     *
     * @return void
     */
    public function testToArraySimple(): void
    {
        // Example from JsonLogic docs: concatenate two strings
        $catRule = new CatRule(['I love', ' pie']);

        $expected = [
            'cat' => [
                'I love',
                ' pie',
            ],
        ];

        $this->assertEquals($expected, $catRule->toArray());
    }

    /**
     * Test with variable insertion (from docs)
     *
     * @return void
     */
    public function testWithVariableInsertion(): void
    {
        // From the docs: "I love apple pie"
        $catRule = new CatRule(['I love ', new VarRule('filling'), ' pie']);

        $expected = [
            'cat' => [
                'I love ',
                ['var' => 'filling'],
                ' pie',
            ],
        ];

        $this->assertEquals($expected, $catRule->toArray());
    }

    /**
     * Test greeting with variable
     *
     * @return void
     */
    public function testGreetingWithVariable(): void
    {
        // "Hello, Dolly" example
        $catRule = new CatRule(['Hello, ', new VarRule('')]);

        $expected = [
            'cat' => [
                'Hello, ',
                ['var' => ''],
            ],
        ];

        $this->assertEquals($expected, $catRule->toArray());
    }

    /**
     * Test with conditional string
     *
     * @return void
     */
    public function testWithConditionalString(): void
    {
        // Create greeting with title based on gender
        $title = new IfRule([
            new VarRule('is_male'),
            'Mr. ',
            'Ms. ',
        ]);

        $catRule = new CatRule([
            'Hello, ',
            $title,
            new VarRule('last_name'),
        ]);

        $expected = [
            'cat' => [
                'Hello, ',
                [
                    'if' => [
                        ['var' => 'is_male'],
                        'Mr. ',
                        'Ms. ',
                    ],
                ],
                ['var' => 'last_name'],
            ],
        ];

        $this->assertEquals($expected, $catRule->toArray());
    }

    /**
     * Test with complex personalized message
     *
     * @return void
     */
    public function testComplexPersonalizedMessage(): void
    {
        // Create message with name and points status
        $greeting = new CatRule(['Hello, ', new VarRule('user.first_name')]);
        $pointsMessage = new IfRule([
            new VarRule('user.points'),
            new CatRule(['! You have ', new VarRule('user.points'), ' points.']),
            '.',
        ]);

        $catRule = new CatRule([$greeting, $pointsMessage]);

        $expected = [
            'cat' => [
                [
                    'cat' => [
                        'Hello, ',
                        ['var' => 'user.first_name'],
                    ],
                ],
                [
                    'if' => [
                        ['var' => 'user.points'],
                        [
                            'cat' => [
                                '! You have ',
                                ['var' => 'user.points'],
                                ' points.',
                            ],
                        ],
                        '.',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $catRule->toArray());
    }

    /**
     * Test fluid interface
     *
     * @return void
     */
    public function testFluidInterface(): void
    {
        $catRule = new CatRule([]);

        $returnedRule = $catRule
            ->setStrings(['Hello'])
            ->addString(', ')
            ->addString(new VarRule('name'));

        $this->assertSame($catRule, $returnedRule);
        $this->assertEquals(['Hello', ', ', new VarRule('name')], $catRule->getStrings());
    }

    /**
     * Test using factory methods
     *
     * @return void
     */
    public function testFactoryMethods(): void
    {
        $rule = JsonLogicRuleFactory::cat([
            'Welcome back, ',
            JsonLogicRuleFactory::var('username'),
            '!',
        ]);

        $expected = [
            'cat' => [
                'Welcome back, ',
                ['var' => 'username'],
                '!',
            ],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }
}
