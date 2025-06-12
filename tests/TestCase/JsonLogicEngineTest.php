<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase;

use Cake\ORM\Entity;
use Cake\TestSuite\TestCase;
use RuleFlow\JsonLogicEngine;
use RuleFlow\JsonLogicEvaluator;

/**
 * JsonLogicEngine Test Case
 */
class JsonLogicEngineTest extends TestCase
{
    /**
     * @var \RuleFlow\JsonLogicEngine
     */
    protected $engine;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->engine = new JsonLogicEngine();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->engine);
        parent::tearDown();
    }

    /**
     * Test instance initialization and interface implementation
     *
     * @return void
     */
    public function testInstance(): void
    {
        $this->assertInstanceOf(JsonLogicEngine::class, $this->engine);
    }

    /**
     * Test getName method
     *
     * @return void
     */
    public function testGetName(): void
    {
        $this->assertEquals('jsonlogic', $this->engine->getName());
    }

    /**
     * Test getEvaluator method
     *
     * @return void
     */
    public function testGetEvaluator(): void
    {
        $evaluator = $this->engine->getEvaluator();
        $this->assertInstanceOf(JsonLogicEvaluator::class, $evaluator);
    }

    /**
     * Test createRule method
     *
     * @return void
     */
    public function testCreateRule(): void
    {
        $rule = $this->engine->createRule('equals', ['field' => 'status', 'value' => 'active']);
        $this->assertIsArray($rule);
        $this->assertArrayHasKey('==', $rule);

        // The rule should be: {"==": [{"var": "status"}, "active"]}
        $this->assertEquals([['var' => 'status'], 'active'], $rule['==']);

        // Check that the rule evaluates correctly
        $entity = new Entity(['status' => 'active']);
        $evaluator = $this->engine->getEvaluator();
        $this->assertTrue($evaluator->evaluate($rule, $entity));

        $entity->set('status', 'inactive');
        $this->assertFalse($evaluator->evaluate($rule, $entity));
    }

    /**
     * Test createRule with different rule types
     *
     * @return void
     */
    public function testCreateRuleTypes(): void
    {
        // Equals rule
        $rule = $this->engine->createRule('equals', ['field' => 'age', 'value' => 25]);
        $this->assertArrayHasKey('==', $rule);

        // Not equals rule
        $rule = $this->engine->createRule('notEquals', ['field' => 'age', 'value' => 25]);
        $this->assertArrayHasKey('!=', $rule);

        // Greater than rule
        $rule = $this->engine->createRule('greaterThan', ['field' => 'age', 'value' => 18]);
        $this->assertArrayHasKey('>', $rule);

        // Less than rule
        $rule = $this->engine->createRule('lessThan', ['field' => 'age', 'value' => 65]);
        $this->assertArrayHasKey('<', $rule);

        // Contains rule
        $rule = $this->engine->createRule('contains', ['field' => 'name', 'value' => 'John']);
        $this->assertArrayHasKey('contains', $rule);

        // AND rule
        $rules = [
            $this->engine->createRule('equals', ['field' => 'active', 'value' => true]),
            $this->engine->createRule('greaterThan', ['field' => 'age', 'value' => 18]),
        ];
        $rule = $this->engine->createRule('and', ['rules' => $rules]);
        $this->assertArrayHasKey('and', $rule);
        $this->assertIsArray($rule['and']);
        $this->assertCount(2, $rule['and']);

        // OR rule
        $rule = $this->engine->createRule('or', ['rules' => $rules]);
        $this->assertArrayHasKey('or', $rule);
        $this->assertIsArray($rule['or']);
        $this->assertCount(2, $rule['or']);

        // NOT rule
        $innerRule = $this->engine->createRule('equals', ['field' => 'active', 'value' => true]);
        $rule = $this->engine->createRule('not', ['rule' => $innerRule]);
        $this->assertArrayHasKey('!', $rule);
    }

    /**
     * Test evaluate method
     *
     * @return void
     */
    public function testEvaluate(): void
    {
        $entity = new Entity([
            'name' => 'John',
            'age' => 25,
            'active' => true,
        ]);

        // Simple rule
        $rule = $this->engine->createRule('equals', ['field' => 'name', 'value' => 'John']);
        $this->assertTrue($this->engine->evaluate($rule, $entity));

        // Complex rule with AND
        $rule = $this->engine->createRule('and', ['rules' => [
            $this->engine->createRule('equals', ['field' => 'name', 'value' => 'John']),
            $this->engine->createRule('greaterThan', ['field' => 'age', 'value' => 18]),
            $this->engine->createRule('equals', ['field' => 'active', 'value' => true]),
        ]]);
        $this->assertTrue($this->engine->evaluate($rule, $entity));

        // Make one condition false
        $entity->set('active', false);
        $this->assertFalse($this->engine->evaluate($rule, $entity));

        // Test with OR
        $rule = $this->engine->createRule('or', ['rules' => [
            $this->engine->createRule('equals', ['field' => 'name', 'value' => 'Jane']),
            $this->engine->createRule('greaterThan', ['field' => 'age', 'value' => 18]),
        ]]);
        $this->assertTrue($this->engine->evaluate($rule, $entity));
    }
}
