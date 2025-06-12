<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Rule\String;

use PHPUnit\Framework\TestCase;
use RuleFlow\Rule\JsonLogicRuleFactory;
use RuleFlow\Rule\String\SubstrRule;
use RuleFlow\Rule\Variable\VarRule;

/**
 * SubstrRule Test
 */
class SubstrRuleTest extends TestCase
{
    /**
     * Test constructor and getters
     *
     * @return void
     */
    public function testConstructorAndGetters(): void
    {
        $string = 'jsonlogic';
        $start = 4;
        $length = 5;

        $substrRule = new SubstrRule($string, $start, $length);

        $this->assertEquals('substr', $substrRule->getOperator());
        $this->assertSame($string, $substrRule->getString());
        $this->assertSame($start, $substrRule->getStart());
        $this->assertSame($length, $substrRule->getLength());
    }

    /**
     * Test constructor with optional length parameter
     *
     * @return void
     */
    public function testConstructorWithOptionalLength(): void
    {
        $string = 'jsonlogic';
        $start = 4;

        $substrRule = new SubstrRule($string, $start);

        $this->assertEquals('substr', $substrRule->getOperator());
        $this->assertSame($string, $substrRule->getString());
        $this->assertSame($start, $substrRule->getStart());
        $this->assertNull($substrRule->getLength());
    }

    /**
     * Test toArray method with all parameters
     *
     * @return void
     */
    public function testToArrayAllParams(): void
    {
        $substrRule = new SubstrRule('jsonlogic', 1, 3);

        $expected = [
            'substr' => [
                'jsonlogic',
                1,
                3,
            ],
        ];

        $this->assertEquals($expected, $substrRule->toArray());
    }

    /**
     * Test toArray method without length parameter
     *
     * @return void
     */
    public function testToArrayWithoutLength(): void
    {
        $substrRule = new SubstrRule('jsonlogic', 4);

        $expected = [
            'substr' => [
                'jsonlogic',
                4,
            ],
        ];

        $this->assertEquals($expected, $substrRule->toArray());
    }

    /**
     * Test examples from JsonLogic docs - positive start position
     *
     * @return void
     */
    public function testPositiveStartPosition(): void
    {
        // Example from JsonLogic docs: {"substr": ["jsonlogic", 4]}
        $substrRule = new SubstrRule('jsonlogic', 4);

        $expected = [
            'substr' => [
                'jsonlogic',
                4,
            ],
        ];

        $this->assertEquals($expected, $substrRule->toArray());
    }

    /**
     * Test examples from JsonLogic docs - negative start position
     *
     * @return void
     */
    public function testNegativeStartPosition(): void
    {
        // Example from JsonLogic docs: {"substr": ["jsonlogic", -5]}
        $substrRule = new SubstrRule('jsonlogic', -5);

        $expected = [
            'substr' => [
                'jsonlogic',
                -5,
            ],
        ];

        $this->assertEquals($expected, $substrRule->toArray());
    }

    /**
     * Test examples from JsonLogic docs - positive start and length
     *
     * @return void
     */
    public function testPositiveStartAndLength(): void
    {
        // Example from JsonLogic docs: {"substr": ["jsonlogic", 1, 3]}
        $substrRule = new SubstrRule('jsonlogic', 1, 3);

        $expected = [
            'substr' => [
                'jsonlogic',
                1,
                3,
            ],
        ];

        $this->assertEquals($expected, $substrRule->toArray());
    }

    /**
     * Test examples from JsonLogic docs - positive start and negative length
     *
     * @return void
     */
    public function testPositiveStartAndNegativeLength(): void
    {
        // Example from JsonLogic docs: {"substr": ["jsonlogic", 4, -2]}
        $substrRule = new SubstrRule('jsonlogic', 4, -2);

        $expected = [
            'substr' => [
                'jsonlogic',
                4,
                -2,
            ],
        ];

        $this->assertEquals($expected, $substrRule->toArray());
    }

    /**
     * Test with variable string
     *
     * @return void
     */
    public function testWithVariableString(): void
    {
        $substrRule = new SubstrRule(new VarRule('domain'), 0, 5);

        $expected = [
            'substr' => [
                ['var' => 'domain'],
                0,
                5,
            ],
        ];

        $this->assertEquals($expected, $substrRule->toArray());
    }

    /**
     * Test with variable start position
     *
     * @return void
     */
    public function testWithVariableStartPosition(): void
    {
        $substrRule = new SubstrRule('jsonlogic', new VarRule('start_position'));

        $expected = [
            'substr' => [
                'jsonlogic',
                ['var' => 'start_position'],
            ],
        ];

        $this->assertEquals($expected, $substrRule->toArray());
    }

    /**
     * Test with variable length
     *
     * @return void
     */
    public function testWithVariableLength(): void
    {
        $substrRule = new SubstrRule('jsonlogic', 0, new VarRule('length'));

        $expected = [
            'substr' => [
                'jsonlogic',
                0,
                ['var' => 'length'],
            ],
        ];

        $this->assertEquals($expected, $substrRule->toArray());
    }

    /**
     * Test using factory methods
     *
     * @return void
     */
    public function testFactoryMethods(): void
    {
        $rule = JsonLogicRuleFactory::substr(
            JsonLogicRuleFactory::var('email'),
            0,
            JsonLogicRuleFactory::var('domain_length'),
        );

        $expected = [
            'substr' => [
                ['var' => 'email'],
                0,
                ['var' => 'domain_length'],
            ],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }
}
