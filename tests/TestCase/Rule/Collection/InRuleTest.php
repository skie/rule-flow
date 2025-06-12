<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Rule\Collection;

use PHPUnit\Framework\TestCase;
use RuleFlow\Rule\Collection\InRule;
use RuleFlow\Rule\Conditional\IfRule;
use RuleFlow\Rule\JsonLogicRuleFactory;
use RuleFlow\Rule\Variable\VarRule;

/**
 * InRule Test
 */
class InRuleTest extends TestCase
{
    /**
     * Test constructor and getters
     *
     * @return void
     */
    public function testConstructorAndGetters(): void
    {
        $value = 'John';
        $collection = ['John', 'Paul', 'George', 'Ringo'];

        $inRule = new InRule($value, $collection);

        $this->assertEquals('in', $inRule->getOperator());
        $this->assertSame($value, $inRule->getValue());
        $this->assertSame($collection, $inRule->getCollection());
    }

    /**
     * Test toArray method with array collection
     *
     * @return void
     */
    public function testToArrayWithArrayCollection(): void
    {
        // Example from JsonLogic docs: {"in":["John", ["John", "Paul", "George", "Ringo"]]}
        $inRule = new InRule('John', ['John', 'Paul', 'George', 'Ringo']);

        $expected = [
            'in' => [
                'John',
                ['John', 'Paul', 'George', 'Ringo'],
            ],
        ];

        $this->assertEquals($expected, $inRule->toArray());
    }

    /**
     * Test toArray method with string collection (substring check)
     *
     * @return void
     */
    public function testToArrayWithStringCollection(): void
    {
        // Example from JsonLogic docs: {"in":["Spring", "Springfield"]}
        $inRule = new InRule('Spring', 'Springfield');

        $expected = [
            'in' => [
                'Spring',
                'Springfield',
            ],
        ];

        $this->assertEquals($expected, $inRule->toArray());
    }

    /**
     * Test with rule value
     *
     * @return void
     */
    public function testWithRuleValue(): void
    {
        // Example from docs with conditional value
        $ifRule = new IfRule([false, 'John', 'Paul']);
        $inRule = new InRule($ifRule, ['John', 'Paul', 'George', 'Ringo']);

        $expected = [
            'in' => [
                [
                    'if' => [
                        false,
                        'John',
                        'Paul',
                    ],
                ],
                ['John', 'Paul', 'George', 'Ringo'],
            ],
        ];

        $this->assertEquals($expected, $inRule->toArray());
    }

    /**
     * Test with variable collection
     *
     * @return void
     */
    public function testWithVariableCollection(): void
    {
        $inRule = new InRule('admin', new VarRule('user_roles'));

        $expected = [
            'in' => [
                'admin',
                ['var' => 'user_roles'],
            ],
        ];

        $this->assertEquals($expected, $inRule->toArray());
    }

    /**
     * Test with variable value and string collection (email domain check)
     *
     * @return void
     */
    public function testWithVariableValueAndStringCollection(): void
    {
        $inRule = new InRule(new VarRule('domain'), 'alloweddomains.com');

        $expected = [
            'in' => [
                ['var' => 'domain'],
                'alloweddomains.com',
            ],
        ];

        $this->assertEquals($expected, $inRule->toArray());
    }

    /**
     * Test using factory methods for array membership
     *
     * @return void
     */
    public function testFactoryMethodsForArrayMembership(): void
    {
        $rule = JsonLogicRuleFactory::in(
            'apple',
            JsonLogicRuleFactory::var('allowed_fruits'),
        );

        $expected = [
            'in' => [
                'apple',
                ['var' => 'allowed_fruits'],
            ],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }

    /**
     * Test using factory methods for substring check
     *
     * @return void
     */
    public function testFactoryMethodsForSubstringCheck(): void
    {
        $rule = JsonLogicRuleFactory::in(
            '.com',
            JsonLogicRuleFactory::var('email'),
        );

        $expected = [
            'in' => [
                '.com',
                ['var' => 'email'],
            ],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }
}
