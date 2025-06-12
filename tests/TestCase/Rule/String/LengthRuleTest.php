<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Rule\String;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuleFlow\CustomRuleRegistry;
use RuleFlow\JsonLogicEvaluator;
use RuleFlow\Rule\String\LengthRule;

/**
 * LengthRule Test
 */
class LengthRuleTest extends TestCase
{
    protected JsonLogicEvaluator $evaluator;

    public function setUp(): void
    {
        parent::setUp();
        $this->evaluator = new JsonLogicEvaluator();

        CustomRuleRegistry::registerRule(LengthRule::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $registry = CustomRuleRegistry::getInstance();
        $reflection = new ReflectionClass($registry);

        $operatorMapProperty = $reflection->getProperty('operatorMap');
        $operatorMapProperty->setAccessible(true);
        $operatorMapProperty->setValue($registry, []);

        $ruleInstancesProperty = $reflection->getProperty('ruleInstances');
        $ruleInstancesProperty->setAccessible(true);
        $ruleInstancesProperty->setValue($registry, []);
    }

    /**
     * Test length of string
     */
    public function testStringLength(): void
    {
        $rule = ['length' => ['var' => 'text']];
        $data = ['text' => 'hello'];

        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertEquals(5, $result);
    }

    /**
     * Test length of array
     */
    public function testArrayLength(): void
    {
        $rule = ['length' => ['var' => 'items']];
        $data = ['items' => ['a', 'b', 'c']];

        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertEquals(3, $result);
    }

    /**
     * Test length of empty string
     */
    public function testEmptyStringLength(): void
    {
        $rule = ['length' => ['var' => 'text']];
        $data = ['text' => ''];

        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertEquals(0, $result);
    }

    /**
     * Test length of empty array
     */
    public function testEmptyArrayLength(): void
    {
        $rule = ['length' => ['var' => 'items']];
        $data = ['items' => []];

        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertEquals(0, $result);
    }

    /**
     * Test length of null value
     */
    public function testNullLength(): void
    {
        $rule = ['length' => ['var' => 'missing']];
        $data = ['text' => 'hello'];

        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertEquals(0, $result);
    }

    /**
     * Test length of numeric value
     */
    public function testNumericLength(): void
    {
        $rule = ['length' => ['var' => 'number']];
        $data = ['number' => 12345];

        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertEquals(5, $result); // strlen('12345') = 5
    }

    /**
     * Test length of boolean value
     */
    public function testBooleanLength(): void
    {
        $rule = ['length' => ['var' => 'flag']];
        $data = ['flag' => true];

        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertEquals(1, $result); // strlen('1') = 1
    }

    /**
     * Test length used in comparison
     */
    public function testLengthInComparison(): void
    {
        $rule = ['>=' => [['length' => ['var' => 'password']], 8]];
        $data = ['password' => 'strongpass'];

        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertTrue($result); // strlen('strongpass') = 10 >= 8
    }

    /**
     * Test length used in comparison - fail case
     */
    public function testLengthInComparisonFail(): void
    {
        $rule = ['>=' => [['length' => ['var' => 'password']], 8]];
        $data = ['password' => 'weak'];

        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertFalse($result); // strlen('weak') = 4 < 8
    }

    /**
     * Test length with literal value
     */
    public function testLengthWithLiteral(): void
    {
        $rule = ['length' => 'test'];
        $data = [];

        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertEquals(4, $result);
    }

    /**
     * Test length in logical AND operation
     */
    public function testLengthInAndOperation(): void
    {
        $rule = [
            'and' => [
                ['>=' => [['length' => ['var' => 'username']], 3]],
                ['<=' => [['length' => ['var' => 'username']], 20]],
            ],
        ];
        $data = ['username' => 'validuser'];

        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertTrue($result); // 9 characters is between 3 and 20
    }

    /**
     * Test length in logical AND operation - fail case
     */
    public function testLengthInAndOperationFail(): void
    {
        $rule = [
            'and' => [
                ['>=' => [['length' => ['var' => 'username']], 3]],
                ['<=' => [['length' => ['var' => 'username']], 20]],
            ],
        ];
        $data = ['username' => 'ab']; // Too short

        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertFalse($result); // 2 characters is < 3
    }

    /**
     * Test rule construction
     */
    public function testRuleConstruction(): void
    {
        $rule = new LengthRule(['var' => 'test']);

        $this->assertEquals('length', $rule->getOperator());
        $this->assertEquals(['var' => 'test'], $rule->getValue());
    }

    /**
     * Test fromArray factory method
     */
    public function testFromArrayFactory(): void
    {
        $rule = LengthRule::fromArray(['var' => 'test']);

        $this->assertInstanceOf(LengthRule::class, $rule);
        $this->assertEquals(['var' => 'test'], $rule->getValue());
    }

    /**
     * Test toArray serialization
     */
    public function testToArraySerialization(): void
    {
        $rule = new LengthRule(['var' => 'test']);
        $array = $rule->toArray();

        $expected = [
            'length' => ['var' => 'test'],
        ];

        $this->assertEquals($expected, $array);
    }
}
