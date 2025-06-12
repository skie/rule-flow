<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Rule\Collection;

use PHPUnit\Framework\TestCase;
use RuleFlow\Rule\Collection\MissingRule;
use RuleFlow\Rule\Conditional\IfRule;
use RuleFlow\Rule\JsonLogicRuleFactory;

/**
 * MissingRule Test
 */
class MissingRuleTest extends TestCase
{
    /**
     * Test constructor and getters
     *
     * @return void
     */
    public function testConstructorAndGetters(): void
    {
        $keys = ['a', 'b', 'c'];

        $missingRule = new MissingRule($keys);

        $this->assertEquals('missing', $missingRule->getOperator());
        $this->assertSame($keys, $missingRule->getKeys());
    }

    /**
     * Test setKeys method
     *
     * @return void
     */
    public function testSetKeys(): void
    {
        $keys1 = ['a', 'b'];
        $missingRule = new MissingRule($keys1);
        $this->assertSame($keys1, $missingRule->getKeys());

        $keys2 = ['c', 'd'];
        $missingRule->setKeys($keys2);
        $this->assertSame($keys2, $missingRule->getKeys());
    }

    /**
     * Test addKey method
     *
     * @return void
     */
    public function testAddKey(): void
    {
        $missingRule = new MissingRule(['a']);
        $this->assertCount(1, $missingRule->getKeys());

        $missingRule->addKey('b');
        $this->assertCount(2, $missingRule->getKeys());
        $this->assertSame('b', $missingRule->getKeys()[1]);

        $missingRule->addKey('c');
        $this->assertCount(3, $missingRule->getKeys());
        $this->assertSame('c', $missingRule->getKeys()[2]);
    }

    /**
     * Test toArray method with simple keys
     *
     * @return void
     */
    public function testToArraySimple(): void
    {
        // Example from JsonLogic docs: {"missing":["a", "b"]}
        $missingRule = new MissingRule(['a', 'b']);

        $expected = [
            'missing' => ['a', 'b'],
        ];

        $this->assertEquals($expected, $missingRule->toArray());
    }

    /**
     * Test with more complex keys
     *
     * @return void
     */
    public function testWithComplexKeys(): void
    {
        // Keys with dot notation
        $missingRule = new MissingRule(['user.firstName', 'user.lastName', 'user.email']);

        $expected = [
            'missing' => ['user.firstName', 'user.lastName', 'user.email'],
        ];

        $this->assertEquals($expected, $missingRule->toArray());
    }

    /**
     * Test used with if statement example from docs
     *
     * @return void
     */
    public function testWithIfCondition(): void
    {
        // Example from JsonLogic docs using missing with if
        $missingRule = new MissingRule(['a', 'b']);
        $ifRule = new IfRule([
            $missingRule,
            'Not enough fruit',
            'OK to proceed',
        ]);

        $expected = [
            'if' => [
                [
                    'missing' => ['a', 'b'],
                ],
                'Not enough fruit',
                'OK to proceed',
            ],
        ];

        $this->assertEquals($expected, $ifRule->toArray());
    }

    /**
     * Test fluid interface
     *
     * @return void
     */
    public function testFluidInterface(): void
    {
        $missingRule = new MissingRule([]);

        $returnedRule = $missingRule
            ->setKeys(['a'])
            ->addKey('b');

        $this->assertSame($missingRule, $returnedRule);
        $this->assertEquals(['a', 'b'], $missingRule->getKeys());
    }

    /**
     * Test using factory methods
     *
     * @return void
     */
    public function testFactoryMethods(): void
    {
        $rule = JsonLogicRuleFactory::missing(['first_name', 'last_name', 'email']);

        $expected = [
            'missing' => ['first_name', 'last_name', 'email'],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }
}
