<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase;

use Cake\TestSuite\TestCase;
use RuleFlow\JsonLogicFactory;

/**
 * JsonLogicFactory Test Case
 */
class JsonLogicFactoryTest extends TestCase
{
    /**
     * @var \RuleFlow\JsonLogicFactory
     */
    protected $factory;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->factory = new JsonLogicFactory();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->factory);
        parent::tearDown();
    }

    /**
     * Test equals rule creation
     *
     * @return void
     */
    public function testEqualsRule(): void
    {
        $rule = $this->factory->equals('status', 'active');
        $this->assertIsArray($rule);
        $this->assertArrayHasKey('==', $rule);
        $this->assertEquals([['var' => 'status'], 'active'], $rule['==']);
    }

    /**
     * Test notEquals rule creation
     *
     * @return void
     */
    public function testNotEqualsRule(): void
    {
        $rule = $this->factory->notEquals('status', 'inactive');
        $this->assertIsArray($rule);
        $this->assertArrayHasKey('!=', $rule);
        $this->assertEquals([['var' => 'status'], 'inactive'], $rule['!=']);
    }

    /**
     * Test greaterThan rule creation
     *
     * @return void
     */
    public function testGreaterThanRule(): void
    {
        $rule = $this->factory->greaterThan('age', 18);
        $this->assertIsArray($rule);
        $this->assertArrayHasKey('>', $rule);
        $this->assertEquals([['var' => 'age'], 18], $rule['>']);
    }

    /**
     * Test lessThan rule creation
     *
     * @return void
     */
    public function testLessThanRule(): void
    {
        $rule = $this->factory->lessThan('age', 65);
        $this->assertIsArray($rule);
        $this->assertArrayHasKey('<', $rule);
        $this->assertEquals([['var' => 'age'], 65], $rule['<']);
    }

    /**
     * Test greaterThanOrEqual rule creation
     *
     * @return void
     */
    public function testGreaterThanOrEqualRule(): void
    {
        $rule = $this->factory->greaterThanOrEqual('age', 18);
        $this->assertIsArray($rule);
        $this->assertArrayHasKey('>=', $rule);
        $this->assertEquals([['var' => 'age'], 18], $rule['>=']);
    }

    /**
     * Test lessThanOrEqual rule creation
     *
     * @return void
     */
    public function testLessThanOrEqualRule(): void
    {
        $rule = $this->factory->lessThanOrEqual('age', 65);
        $this->assertIsArray($rule);
        $this->assertArrayHasKey('<=', $rule);
        $this->assertEquals([['var' => 'age'], 65], $rule['<=']);
    }

    /**
     * Test contains rule creation
     *
     * @return void
     */
    public function testContainsRule(): void
    {
        $rule = $this->factory->contains('name', 'John');
        $this->assertIsArray($rule);
        $this->assertArrayHasKey('contains', $rule);
        $this->assertEquals([['var' => 'name'], 'John'], $rule['contains']);
    }

    /**
     * Test startsWith rule creation
     *
     * @return void
     */
    public function testStartsWithRule(): void
    {
        $rule = $this->factory->startsWith('name', 'J');
        $this->assertIsArray($rule);
        $this->assertArrayHasKey('startsWith', $rule);
        $this->assertEquals([['var' => 'name'], 'J'], $rule['startsWith']);
    }

    /**
     * Test endsWith rule creation
     *
     * @return void
     */
    public function testEndsWithRule(): void
    {
        $rule = $this->factory->endsWith('name', 'n');
        $this->assertIsArray($rule);
        $this->assertArrayHasKey('endsWith', $rule);
        $this->assertEquals([['var' => 'name'], 'n'], $rule['endsWith']);
    }

    /**
     * Test in rule creation
     *
     * @return void
     */
    public function testInRule(): void
    {
        $rule = $this->factory->in('role', ['admin', 'manager']);
        $this->assertIsArray($rule);
        $this->assertArrayHasKey('in', $rule);
        $this->assertEquals([['var' => 'role'], ['admin', 'manager']], $rule['in']);
    }

    /**
     * Test AND rule creation
     *
     * @return void
     */
    public function testAndRule(): void
    {
        $rules = [
            $this->factory->equals('active', true),
            $this->factory->greaterThan('age', 18),
        ];

        $rule = $this->factory->and($rules);
        $this->assertIsArray($rule);
        $this->assertArrayHasKey('and', $rule);
        $this->assertIsArray($rule['and']);
        $this->assertCount(2, $rule['and']);

        // Verify that the AND rule contains our sub-rules
        $this->assertArrayHasKey('==', $rule['and'][0]);
        $this->assertArrayHasKey('>', $rule['and'][1]);
    }

    /**
     * Test OR rule creation
     *
     * @return void
     */
    public function testOrRule(): void
    {
        $rules = [
            $this->factory->equals('role', 'admin'),
            $this->factory->equals('role', 'manager'),
        ];

        $rule = $this->factory->or($rules);
        $this->assertIsArray($rule);
        $this->assertArrayHasKey('or', $rule);
        $this->assertIsArray($rule['or']);
        $this->assertCount(2, $rule['or']);

        // Verify that the OR rule contains our sub-rules
        $this->assertArrayHasKey('==', $rule['or'][0]);
        $this->assertArrayHasKey('==', $rule['or'][1]);
    }

    /**
     * Test NOT rule creation
     *
     * @return void
     */
    public function testNotRule(): void
    {
        $innerRule = $this->factory->equals('active', true);

        $rule = $this->factory->not($innerRule);
        $this->assertIsArray($rule);
        $this->assertArrayHasKey('!', $rule);

        // Verify that the NOT rule contains our sub-rule
        $this->assertArrayHasKey('==', $rule['!']);
    }

    /**
     * Test complex rule creation
     *
     * @return void
     */
    public function testComplexRuleCombination(): void
    {
        // Create a complex rule: (active AND age >= 18) OR role == 'admin'
        $rule = $this->factory->or([
            $this->factory->and([
                $this->factory->equals('active', true),
                $this->factory->greaterThanOrEqual('age', 18),
            ]),
            $this->factory->equals('role', 'admin'),
        ]);

        $this->assertIsArray($rule);
        $this->assertArrayHasKey('or', $rule);
        $this->assertIsArray($rule['or']);
        $this->assertCount(2, $rule['or']);

        // Verify first branch is an AND rule
        $this->assertArrayHasKey('and', $rule['or'][0]);

        // Verify second branch is an equals rule
        $this->assertArrayHasKey('==', $rule['or'][1]);

        // Verify the AND rule has two conditions
        $this->assertCount(2, $rule['or'][0]['and']);
        $this->assertArrayHasKey('==', $rule['or'][0]['and'][0]);
        $this->assertArrayHasKey('>=', $rule['or'][0]['and'][1]);
    }
}
