<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Rule\String;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuleFlow\CustomRuleRegistry;
use RuleFlow\JsonLogicEvaluator;
use RuleFlow\Rule\String\MatchRule;

/**
 * MatchRule Test
 */
class MatchRuleTest extends TestCase
{
    protected JsonLogicEvaluator $evaluator;

    public function setUp(): void
    {
        parent::setUp();
        $this->evaluator = new JsonLogicEvaluator();

        // Register the custom rule
        CustomRuleRegistry::registerRule(MatchRule::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        // Clear the registry after each test
        $registry = CustomRuleRegistry::getInstance();
        $reflection = new ReflectionClass($registry);

        // Clear operatorMap
        $operatorMapProperty = $reflection->getProperty('operatorMap');
        $operatorMapProperty->setAccessible(true);
        $operatorMapProperty->setValue($registry, []);

        // Clear ruleInstances
        $ruleInstancesProperty = $reflection->getProperty('ruleInstances');
        $ruleInstancesProperty->setAccessible(true);
        $ruleInstancesProperty->setValue($registry, []);
    }

    /**
     * Test email pattern matching
     */
    public function testEmailPattern(): void
    {
        $rule = ['match' => [['var' => 'email'], '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$']];
        $data = ['email' => 'user@example.com'];

        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertTrue($result);
    }

    /**
     * Test email pattern matching - fail case
     */
    public function testEmailPatternFail(): void
    {
        $rule = ['match' => [['var' => 'email'], '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$']];
        $data = ['email' => 'invalid.email'];

        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertFalse($result);
    }

    /**
     * Test phone number pattern
     */
    public function testPhonePattern(): void
    {
        $rule = ['match' => [['var' => 'phone'], '^\\+[1-9]\\d{1,14}$']];
        $data = ['phone' => '+1234567890'];

        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertTrue($result);
    }

    /**
     * Test pattern with flags
     */
    public function testPatternWithFlags(): void
    {
        $rule = ['match' => [['var' => 'text'], '[a-z]+', 'i']];
        $data = ['text' => 'HELLO'];

        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertTrue($result); // Case insensitive match
    }

    /**
     * Test pattern already with delimiters
     */
    public function testPatternWithDelimiters(): void
    {
        $rule = ['match' => [['var' => 'text'], '/^hello$/']];
        $data = ['text' => 'hello'];

        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertTrue($result);
    }

    /**
     * Test empty string matching
     */
    public function testEmptyStringMatching(): void
    {
        $rule = ['match' => [['var' => 'text'], '^$']];
        $data = ['text' => ''];

        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertTrue($result);
    }

    /**
     * Test empty pattern
     */
    public function testEmptyPattern(): void
    {
        $rule = ['match' => [['var' => 'text'], '']];
        $data = ['text' => 'hello'];

        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertFalse($result);
    }

    /**
     * Test missing data
     */
    public function testMissingData(): void
    {
        $rule = ['match' => [['var' => 'missing'], 'test']];
        $data = ['text' => 'hello'];

        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertFalse($result);
    }

    /**
     * Test invalid regex pattern
     */
    public function testInvalidRegexPattern(): void
    {
        $rule = ['match' => [['var' => 'text'], '[invalid(']];
        $data = ['text' => 'hello'];

        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertFalse($result); // Invalid regex should return false
    }

    /**
     * Test literal string matching
     */
    public function testLiteralStringMatching(): void
    {
        $rule = ['match' => ['hello', '^hello$']];
        $data = [];

        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertTrue($result);
    }

    /**
     * Test numeric value matching
     */
    public function testNumericValueMatching(): void
    {
        $rule = ['match' => [['var' => 'number'], '^\\d+$']];
        $data = ['number' => 12345];

        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertTrue($result);
    }

    /**
     * Test alphanumeric pattern
     */
    public function testAlphanumericPattern(): void
    {
        $rule = ['match' => [['var' => 'username'], '^[a-zA-Z0-9]+$']];
        $data = ['username' => 'user123'];

        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertTrue($result);
    }

    /**
     * Test alphanumeric pattern - fail case
     */
    public function testAlphanumericPatternFail(): void
    {
        $rule = ['match' => [['var' => 'username'], '^[a-zA-Z0-9]+$']];
        $data = ['username' => 'user-123'];

        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertFalse($result); // Contains hyphen
    }

    /**
     * Test password complexity pattern
     */
    public function testPasswordComplexityPattern(): void
    {
        $rule = ['match' => [
            ['var' => 'password'],
            '^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)[a-zA-Z\\d@$!%*?&]{8,}$',
        ]];
        $data = ['password' => 'StrongPass123'];

        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertTrue($result);
    }

    /**
     * Test password complexity pattern - fail case
     */
    public function testPasswordComplexityPatternFail(): void
    {
        $rule = ['match' => [
            ['var' => 'password'],
            '^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)[a-zA-Z\\d@$!%*?&]{8,}$',
        ]];
        $data = ['password' => 'weak'];

        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertFalse($result); // Too weak
    }

    /**
     * Test match in logical operation
     */
    public function testMatchInLogicalOperation(): void
    {
        $rule = [
            'and' => [
                ['match' => [['var' => 'email'], '@']],
                ['match' => [['var' => 'email'], '\\.']],
            ],
        ];
        $data = ['email' => 'user@example.com'];

        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertTrue($result); // Contains both @ and .
    }

    /**
     * Test insufficient parameters
     */
    public function testInsufficientParameters(): void
    {
        $rule = ['match' => [['var' => 'text']]];
        $data = ['text' => 'hello'];

        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertFalse($result); // Not enough parameters
    }

    /**
     * Test rule construction
     */
    public function testRuleConstruction(): void
    {
        $rule = new MatchRule(['var' => 'text'], 'pattern');

        $this->assertEquals('match', $rule->getOperator());
        $this->assertEquals(['var' => 'text'], $rule->getString());
        $this->assertEquals('pattern', $rule->getPattern());
        $this->assertNull($rule->getFlags());
    }

    /**
     * Test rule construction with flags
     */
    public function testRuleConstructionWithFlags(): void
    {
        $rule = new MatchRule(['var' => 'text'], 'pattern', 'i');

        $this->assertEquals('match', $rule->getOperator());
        $this->assertEquals(['var' => 'text'], $rule->getString());
        $this->assertEquals('pattern', $rule->getPattern());
        $this->assertEquals('i', $rule->getFlags());
    }

    /**
     * Test fromArray factory method
     */
    public function testFromArrayFactory(): void
    {
        $data = ['string' => ['var' => 'text'], 'pattern' => 'test', 'flags' => 'i'];
        $rule = MatchRule::fromArray($data);

        $this->assertInstanceOf(MatchRule::class, $rule);
        $this->assertEquals(['var' => 'text'], $rule->getString());
        $this->assertEquals('test', $rule->getPattern());
        $this->assertEquals('i', $rule->getFlags());
    }

    /**
     * Test fromArray factory method with array indices
     */
    public function testFromArrayFactoryWithIndices(): void
    {
        $data = [['var' => 'text'], 'test', 'i'];
        $rule = MatchRule::fromArray($data);

        $this->assertInstanceOf(MatchRule::class, $rule);
        $this->assertEquals(['var' => 'text'], $rule->getString());
        $this->assertEquals('test', $rule->getPattern());
        $this->assertEquals('i', $rule->getFlags());
    }

    /**
     * Test toArray serialization
     */
    public function testToArraySerialization(): void
    {
        $rule = new MatchRule(['var' => 'text'], 'pattern');
        $array = $rule->toArray();

        $expected = [
            'match' => [
                ['var' => 'text'],
                'pattern',
            ],
        ];

        $this->assertEquals($expected, $array);
    }

    /**
     * Test toArray serialization with flags
     */
    public function testToArraySerializationWithFlags(): void
    {
        $rule = new MatchRule(['var' => 'text'], 'pattern', 'i');
        $array = $rule->toArray();

        $expected = [
            'match' => [
                ['var' => 'text'],
                'pattern',
                'i',
            ],
        ];

        $this->assertEquals($expected, $array);
    }
}
