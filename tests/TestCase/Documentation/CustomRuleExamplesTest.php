<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Documentation;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuleFlow\CustomRuleInterface;
use RuleFlow\CustomRuleRegistry;
use RuleFlow\JsonLogicEvaluator;
use RuleFlow\Rule\AbstractJsonLogicRule;
use RuleFlow\Rule\String\LengthRule;
use RuleFlow\Rule\String\MatchRule;

/**
 * Custom Rule Examples Test
 *
 * Tests all the custom rule examples from the documentation to ensure they work correctly
 */
class CustomRuleExamplesTest extends TestCase
{
    protected JsonLogicEvaluator $evaluator;

    public function setUp(): void
    {
        parent::setUp();
        $this->evaluator = new JsonLogicEvaluator();
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
     * Test AgeVerificationRule from documentation example
     */
    public function testAgeVerificationRuleExample(): void
    {
        // Create the AgeVerificationRule class from documentation
        $ageRule = new class(18) extends AbstractJsonLogicRule implements CustomRuleInterface {
            protected int $minAge;

            public function __construct(int $minAge = 18)
            {
                $this->operator = 'age_verification';
                $this->minAge = $minAge;
            }

            public function evaluate(mixed $resolvedValues, mixed $data): mixed
            {
                $birthDate = $resolvedValues; // Single operand
                if (!$birthDate) {
                    return false;
                }

                $age = (new \DateTime())->diff(new \DateTime($birthDate))->y;
                return $age >= $this->minAge;
            }

            protected function getOperands(): mixed
            {
                return $this->minAge;
            }
        };

        // Register the rule
        CustomRuleRegistry::getInstance()->register(get_class($ageRule));

        // Test case 1: Valid age (over 18)
        $rule = ['age_verification' => '1990-05-15'];
        $data = ['birth_date' => '1990-05-15'];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertTrue($result, 'Person born in 1990 should be over 18');

        // Test case 2: Invalid age (under 18)
        $rule = ['age_verification' => '2010-05-15'];
        $data = ['birth_date' => '2010-05-15'];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertFalse($result, 'Person born in 2010 should be under 18');

        // Test case 3: Edge case - exactly 18
        $eighteenYearsAgo = (new \DateTime())->sub(new \DateInterval('P18Y'))->format('Y-m-d');
        $rule = ['age_verification' => $eighteenYearsAgo];
        $data = ['birth_date' => $eighteenYearsAgo];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertTrue($result, 'Person exactly 18 should be valid');

        // Test case 4: Empty birth date
        $rule = ['age_verification' => ''];
        $data = ['birth_date' => ''];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertFalse($result, 'Empty birth date should be invalid');

        // Test case 5: Different minimum age (21)
        $ageRule21 = new class(21) extends AbstractJsonLogicRule implements CustomRuleInterface {
            protected int $minAge;

            public function __construct(int $minAge = 21)
            {
                $this->operator = 'age_verification_21';
                $this->minAge = $minAge;
            }

            public function evaluate(mixed $resolvedValues, mixed $data): mixed
            {
                $birthDate = $resolvedValues;
                if (!$birthDate) {
                    return false;
                }

                $age = (new \DateTime())->diff(new \DateTime($birthDate))->y;
                return $age >= $this->minAge;
            }

            protected function getOperands(): mixed
            {
                return $this->minAge;
            }
        };

        CustomRuleRegistry::getInstance()->register(get_class($ageRule21));

        $rule = ['age_verification_21' => '2005-05-15'];
        $data = ['birth_date' => '2005-05-15'];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertFalse($result, 'Person born in 2005 should be under 21');
    }

    /**
     * Test built-in LengthRule examples from documentation
     */
    public function testBuiltInLengthRuleExamples(): void
    {
        // Register built-in LengthRule
        CustomRuleRegistry::registerRule(LengthRule::class);

        // Test case 1: Basic length operation
        $rule = ['length' => ['var' => 'password']];
        $data = ['password' => 'mypassword'];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertEquals(10, $result, 'Password length should be 10');

        // Test case 2: Minimum length validation (>=)
        $rule = ['>=' => [['length' => ['var' => 'password']], 8]];
        $data = ['password' => 'mypassword'];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertTrue($result, 'Password should meet minimum length requirement');

        // Test case 3: Password too short
        $rule = ['>=' => [['length' => ['var' => 'password']], 8]];
        $data = ['password' => 'short'];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertFalse($result, 'Short password should fail minimum length requirement');

        // Test case 4: Array length
        $rule = ['length' => ['var' => 'items']];
        $data = ['items' => ['item1', 'item2', 'item3']];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertEquals(3, $result, 'Array length should be 3');

        // Test case 5: Empty string
        $rule = ['length' => ['var' => 'text']];
        $data = ['text' => ''];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertEquals(0, $result, 'Empty string length should be 0');

        // Test case 6: Null value
        $rule = ['length' => ['var' => 'missing']];
        $data = [];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertEquals(0, $result, 'Missing value length should be 0');
    }

    /**
     * Test built-in MatchRule examples from documentation
     */
    public function testBuiltInMatchRuleExamples(): void
    {
        // Register built-in MatchRule
        CustomRuleRegistry::registerRule(MatchRule::class);

        // Test case 1: Email validation pattern
        $emailPattern = '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$';
        $rule = ['match' => [['var' => 'email'], $emailPattern]];

        // Valid email
        $data = ['email' => 'user@example.com'];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertTrue($result, 'Valid email should match pattern');

        // Invalid email
        $data = ['email' => 'invalid.email'];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertFalse($result, 'Invalid email should not match pattern');

        // Test case 2: Phone number validation
        $phonePattern = '^\\+[1-9]\\d{1,14}$';
        $rule = ['match' => [['var' => 'phone'], $phonePattern]];

        // Valid international phone
        $data = ['phone' => '+1234567890'];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertTrue($result, 'Valid international phone should match pattern');

        // Invalid phone (no plus)
        $data = ['phone' => '1234567890'];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertFalse($result, 'Phone without + should not match international pattern');

        // Test case 3: Password strength pattern
        $passwordPattern = '^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)[a-zA-Z\\d@$!%*?&]{8,}$';
        $rule = ['match' => [['var' => 'password'], $passwordPattern]];

        // Strong password
        $data = ['password' => 'MyPass123'];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertTrue($result, 'Strong password should match pattern');

        // Weak password (no uppercase)
        $data = ['password' => 'mypass123'];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertFalse($result, 'Weak password should not match pattern');
    }

    /**
     * Test server-side controller usage examples from documentation
     */
    public function testServerSideControllerExamples(): void
    {
        // Register built-in rules
        CustomRuleRegistry::registerRule(MatchRule::class);

        // Create AgeVerificationRule for this test
        $ageRule = new class(18) extends AbstractJsonLogicRule implements CustomRuleInterface {
            protected int $minAge;

            public function __construct(int $minAge = 18)
            {
                $this->operator = 'age_verification';
                $this->minAge = $minAge;
            }

            public function evaluate(mixed $resolvedValues, mixed $data): mixed
            {
                $birthDate = $resolvedValues;
                if (!$birthDate) {
                    return false;
                }

                $age = (new \DateTime())->diff(new \DateTime($birthDate))->y;
                return $age >= $this->minAge;
            }

            protected function getOperands(): mixed
            {
                return $this->minAge;
            }
        };

        CustomRuleRegistry::getInstance()->register(get_class($ageRule));

        // Test the controller example from documentation
        $rules = [
            'email' => [
                ['match' => [['var' => 'email'], '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$']]
            ],
            'birth_date' => [
                ['age_verification' => ['var' => 'birth_date']]
            ]
        ];

        $data = [
            'email' => 'user@example.com',
            'birth_date' => '1990-05-15'
        ];

        // Test email validation
        $emailResult = $this->evaluator->evaluate($rules['email'][0], $data);
        $this->assertTrue($emailResult, 'Valid email should pass validation');

        // Test age verification
        $ageResult = $this->evaluator->evaluate($rules['birth_date'][0], $data);
        $this->assertTrue($ageResult, 'Valid age should pass verification');

        // Test with invalid data
        $invalidData = [
            'email' => 'invalid.email',
            'birth_date' => '2010-05-15'
        ];

        $emailResult = $this->evaluator->evaluate($rules['email'][0], $invalidData);
        $this->assertFalse($emailResult, 'Invalid email should fail validation');

        $ageResult = $this->evaluator->evaluate($rules['birth_date'][0], $invalidData);
        $this->assertFalse($ageResult, 'Invalid age should fail verification');
    }

    /**
     * Test common validation patterns from documentation
     */
    public function testCommonValidationPatterns(): void
    {
        CustomRuleRegistry::registerRule(MatchRule::class);

        // Email validation pattern
        $emailPattern = '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$';
        $rule = ['match' => [['var' => 'email'], $emailPattern]];

        $testCases = [
            ['email' => 'test@example.com', 'expected' => true],
            ['email' => 'user.name@domain.co.uk', 'expected' => true],
            ['email' => 'invalid.email', 'expected' => false],
            ['email' => '@domain.com', 'expected' => false],
            ['email' => 'user@', 'expected' => false],
        ];

        foreach ($testCases as $testCase) {
            $result = $this->evaluator->evaluate($rule, $testCase);
            $this->assertEquals(
                $testCase['expected'],
                $result,
                "Email '{$testCase['email']}' should " . ($testCase['expected'] ? 'pass' : 'fail')
            );
        }

        // Phone number validation pattern
        $phonePattern = '^\\+[1-9]\\d{1,14}$';
        $rule = ['match' => [['var' => 'phone'], $phonePattern]];

        $phoneTestCases = [
            ['phone' => '+12345678901', 'expected' => true],
            ['phone' => '+441234567890', 'expected' => true],
            ['phone' => '12345', 'expected' => false],
            ['phone' => '+0123456789', 'expected' => false],
            ['phone' => 'abc', 'expected' => false],
        ];

        foreach ($phoneTestCases as $testCase) {
            $result = $this->evaluator->evaluate($rule, $testCase);
            $this->assertEquals(
                $testCase['expected'],
                $result,
                "Phone '{$testCase['phone']}' should " . ($testCase['expected'] ? 'pass' : 'fail')
            );
        }
    }

    /**
     * Test debugging examples from documentation
     */
    public function testDebuggingExamples(): void
    {
        // Create AgeVerificationRule for debugging test
        $ageRule = new class(18) extends AbstractJsonLogicRule implements CustomRuleInterface {
            protected int $minAge;

            public function __construct(int $minAge = 18)
            {
                $this->operator = 'age_verification';
                $this->minAge = $minAge;
            }

            public function evaluate(mixed $resolvedValues, mixed $data): mixed
            {
                $birthDate = $resolvedValues;
                if (!$birthDate) {
                    return false;
                }

                $age = (new \DateTime())->diff(new \DateTime($birthDate))->y;
                return $age >= $this->minAge;
            }

            protected function getOperands(): mixed
            {
                return $this->minAge;
            }
        };

        CustomRuleRegistry::getInstance()->register(get_class($ageRule));

        // Test the debugging example from documentation
        $rule = ['age_verification' => '1990-01-01'];
        $testData = ['birth_date' => '1990-01-01'];

        $result = $this->evaluator->evaluate($rule, $testData);
        $this->assertTrue($result, 'Debugging example should return true for valid birth date');

        // Test with invalid data for debugging
        $rule = ['age_verification' => '2010-01-01'];
        $testData = ['birth_date' => '2010-01-01'];

        $result = $this->evaluator->evaluate($rule, $testData);
        $this->assertFalse($result, 'Debugging example should return false for invalid birth date');

        // Test edge cases for debugging
        $rule = ['age_verification' => null];
        $testData = ['birth_date' => null];

        $result = $this->evaluator->evaluate($rule, $testData);
        $this->assertFalse($result, 'Debugging example should handle null values gracefully');
    }

    /**
     * Test complex validation scenarios combining multiple custom rules
     */
    public function testComplexValidationScenarios(): void
    {
        // Register all needed rules
        CustomRuleRegistry::registerRule(LengthRule::class);
        CustomRuleRegistry::registerRule(MatchRule::class);

        // Create AgeVerificationRule
        $ageRule = new class(18) extends AbstractJsonLogicRule implements CustomRuleInterface {
            protected int $minAge;

            public function __construct(int $minAge = 18)
            {
                $this->operator = 'age_verification';
                $this->minAge = $minAge;
            }

            public function evaluate(mixed $resolvedValues, mixed $data): mixed
            {
                $birthDate = $resolvedValues;
                if (!$birthDate) {
                    return false;
                }

                $age = (new \DateTime())->diff(new \DateTime($birthDate))->y;
                return $age >= $this->minAge;
            }

            protected function getOperands(): mixed
            {
                return $this->minAge;
            }
        };

        CustomRuleRegistry::getInstance()->register(get_class($ageRule));

        // Complex validation: user registration form
        $userData = [
            'email' => 'user@example.com',
            'password' => 'MySecurePass123',
            'birth_date' => '1990-05-15',
            'username' => 'johndoe'
        ];

        // Email validation
        $emailRule = ['match' => [['var' => 'email'], '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$']];
        $emailValid = $this->evaluator->evaluate($emailRule, $userData);
        $this->assertTrue($emailValid, 'Email should be valid');

        // Password length validation
        $passwordLengthRule = ['>=' => [['length' => ['var' => 'password']], 8]];
        $passwordLengthValid = $this->evaluator->evaluate($passwordLengthRule, $userData);
        $this->assertTrue($passwordLengthValid, 'Password should meet length requirement');

        // Age verification
        $ageRule = ['age_verification' => ['var' => 'birth_date']];
        $ageValid = $this->evaluator->evaluate($ageRule, $userData);
        $this->assertTrue($ageValid, 'Age should be valid');

        // Username length validation
        $usernameLengthRule = ['and' => [
            ['>=' => [['length' => ['var' => 'username']], 3]],
            ['<=' => [['length' => ['var' => 'username']], 20]]
        ]];
        $usernameValid = $this->evaluator->evaluate($usernameLengthRule, $userData);
        $this->assertTrue($usernameValid, 'Username should meet length requirements');

        // Combined validation rule
        $combinedRule = ['and' => [
            ['match' => [['var' => 'email'], '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$']],
            ['>=' => [['length' => ['var' => 'password']], 8]],
            ['age_verification' => ['var' => 'birth_date']],
            ['and' => [
                ['>=' => [['length' => ['var' => 'username']], 3]],
                ['<=' => [['length' => ['var' => 'username']], 20]]
            ]]
        ]];

        $allValid = $this->evaluator->evaluate($combinedRule, $userData);
        $this->assertTrue($allValid, 'All validation rules should pass for valid user data');

        // Test with invalid data
        $invalidUserData = [
            'email' => 'invalid.email',
            'password' => 'short',
            'birth_date' => '2010-05-15',
            'username' => 'ab'
        ];

        $allValid = $this->evaluator->evaluate($combinedRule, $invalidUserData);
        $this->assertFalse($allValid, 'Validation should fail for invalid user data');
    }
}
