<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Rule\Variable;

use PHPUnit\Framework\TestCase;
use RuleFlow\Rule\JsonLogicRuleFactory;
use RuleFlow\Rule\Variable\VarRule;

/**
 * VarRule Test
 */
class VarRuleTest extends TestCase
{
    /**
     * Test constructor and getters with string path
     *
     * @return void
     */
    public function testConstructorAndGettersWithStringPath(): void
    {
        $path = 'user.name';
        $defaultValue = 'Guest';

        $varRule = new VarRule($path, $defaultValue);

        $this->assertEquals('var', $varRule->getOperator());
        $this->assertSame($path, $varRule->getPath());
        $this->assertSame($defaultValue, $varRule->getDefaultValue());
    }

    /**
     * Test constructor and getters with array path
     *
     * @return void
     */
    public function testConstructorAndGettersWithArrayPath(): void
    {
        $path = ['user', 'profile', 'name'];
        $defaultValue = 'Anonymous';

        $varRule = new VarRule($path, $defaultValue);

        $this->assertEquals('var', $varRule->getOperator());
        $this->assertSame($path, $varRule->getPath());
        $this->assertSame($defaultValue, $varRule->getDefaultValue());
    }

    /**
     * Test toArray method with simple path and no default
     *
     * @return void
     */
    public function testToArraySimpleNoDefault(): void
    {
        // Example: {"var": "user.name"}
        $varRule = new VarRule('user.name');

        $expected = [
            'var' => 'user.name',
        ];

        $this->assertEquals($expected, $varRule->toArray());
    }

    /**
     * Test toArray method with simple path and default value
     *
     * @return void
     */
    public function testToArraySimpleWithDefault(): void
    {
        // Example: {"var": ["user.name", "Guest"]}
        $varRule = new VarRule('user.name', 'Guest');

        $expected = [
            'var' => ['user.name', 'Guest'],
        ];

        $this->assertEquals($expected, $varRule->toArray());
    }

    /**
     * Test with array path and no default
     *
     * @return void
     */
    public function testWithArrayPathNoDefault(): void
    {
        // Example: {"var": ["user", "profile", "name"]}
        $varRule = new VarRule(['user', 'profile', 'name']);

        $expected = [
            'var' => ['user', 'profile', 'name'],
        ];

        $this->assertEquals($expected, $varRule->toArray());
    }

    /**
     * Test with array path and default value
     *
     * @return void
     */
    public function testWithArrayPathAndDefault(): void
    {
        // Example: {"var": [["user", "profile", "name"], "Anonymous"]}
        $varRule = new VarRule(['user', 'profile', 'name'], 'Anonymous');

        $expected = [
            'var' => [
                ['user', 'profile', 'name'],
                'Anonymous',
            ],
        ];

        $this->assertEquals($expected, $varRule->toArray());
    }

    /**
     * Test with current data context (empty string path)
     *
     * @return void
     */
    public function testWithCurrentDataContext(): void
    {
        // Example: {"var": ""} - returns the current data context
        $varRule = new VarRule('');

        $expected = [
            'var' => '',
        ];

        $this->assertEquals($expected, $varRule->toArray());
    }

    /**
     * Test with complex default value
     *
     * @return void
     */
    public function testWithComplexDefaultValue(): void
    {
        // Example: Default value as an array
        $varRule = new VarRule('user.roles', ['guest']);

        $expected = [
            'var' => ['user.roles', ['guest']],
        ];

        $this->assertEquals($expected, $varRule->toArray());
    }

    /**
     * Test with null default value
     *
     * @return void
     */
    public function testWithNullDefaultValue(): void
    {
        // Example: Explicit null default value
        $varRule = new VarRule('user.settings', null);

        // Since null is the default for default value, it shouldn't be included
        $expected = [
            'var' => 'user.settings',
        ];

        $this->assertEquals($expected, $varRule->toArray());
    }

    /**
     * Test with rule object as default value
     *
     * @return void
     */
    public function testWithRuleObjectAsDefaultValue(): void
    {
        // Example: Default value as another VarRule
        $defaultRule = new VarRule('defaults.userName');
        $varRule = new VarRule('user.name', $defaultRule);

        $expected = [
            'var' => [
                'user.name',
                ['var' => 'defaults.userName'],
            ],
        ];

        $this->assertEquals($expected, $varRule->toArray());
    }

    /**
     * Test using factory methods
     *
     * @return void
     */
    public function testFactoryMethods(): void
    {
        $rule = JsonLogicRuleFactory::var('user.email', 'no-email@example.com');

        $expected = [
            'var' => ['user.email', 'no-email@example.com'],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }
}
