<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Validation;

use Cake\TestSuite\TestCase;
use Cake\Validation\ValidationRule;
use Cake\Validation\Validator;
use InvalidArgumentException;
use RuleFlow\JsonLogicEvaluator;
use RuleFlow\Validation\ValidationRuleConverter;

/**
 * ValidationRuleConverter Test Case
 */
class ValidationRuleConverterTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \RuleFlow\Validation\ValidationRuleConverter
     */
    protected ValidationRuleConverter $converter;

    /**
     * @var JsonLogicEvaluator
     */
    protected JsonLogicEvaluator $evaluator;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->converter = new ValidationRuleConverter();
        $this->evaluator = new JsonLogicEvaluator();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->converter);
        parent::tearDown();
    }

    /**
     * Test convertValidator method with basic rules
     *
     * @return void
     */
    public function testConvertValidatorBasicRules(): void
    {
        $validator = new Validator();
        $validator
            ->equals('status', 'active')
            ->greaterThan('age', 18)
            ->notBlank('name');

        $result = $this->converter->convertValidator($validator);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('age', $result);
        $this->assertArrayHasKey('name', $result);

        foreach ($result as $fieldData) {
            $this->assertArrayHasKey('rules', $fieldData);
            $this->assertIsArray($fieldData['rules']);
            $this->assertNotEmpty($fieldData['rules']);

            foreach ($fieldData['rules'] as $ruleData) {
                $this->assertArrayHasKey('rule', $ruleData);
                $this->assertArrayHasKey('message', $ruleData);
                $this->assertIsArray($ruleData['rule']);
                $this->assertIsString($ruleData['message']);
            }
        }
    }

    /**
     * Test convertValidator method with multiple rules per field
     *
     * @return void
     */
    public function testConvertValidatorMultipleRulesPerField(): void
    {
        $validator = new Validator();
        $validator
            ->greaterThan('age', 18)
            ->lessThan('age', 65);

        $result = $this->converter->convertValidator($validator);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertArrayHasKey('age', $result);

        $ageField = $result['age'];
        $this->assertArrayHasKey('rules', $ageField);
        $this->assertCount(2, $ageField['rules']);

        foreach ($ageField['rules'] as $ruleData) {
            $this->assertArrayHasKey('rule', $ruleData);
            $this->assertArrayHasKey('message', $ruleData);
            $this->assertIsArray($ruleData['rule']);
            $this->assertIsString($ruleData['message']);
        }
    }

    /**
     * Test equals rule conversion
     *
     * @return void
     */
    public function testEquals(): void
    {
        $validator = new Validator();
        $validator->equals('status', 'active');

        $result = $this->converter->convertValidator($validator);
        $statusRule = $result['status'];

        $ruleArray = $statusRule['rules'][0]['rule'];
        $this->assertArrayHasKey('==', $ruleArray);
        $this->assertEquals([['var' => 'status'], 'active'], $ruleArray['==']);

        $source = ['status' => 'active'];

        $jsonLogicResult = $this->evaluator->evaluate($ruleArray, $source);
        $this->assertTrue($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertEmpty($cakeResult);

        $source = ['status' => 'inactive'];

        $jsonLogicResult = $this->evaluator->evaluate($ruleArray, $source);
        $this->assertFalse($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertNotEmpty($cakeResult);
    }

    /**
     * Test notEquals rule conversion
     *
     * @return void
     */
    public function testNotEquals(): void
    {
        $validator = new Validator();
        $validator->notEquals('status', 'inactive');

        $result = $this->converter->convertValidator($validator);
        $statusRule = $result['status'];

        $ruleArray = $statusRule['rules'][0]['rule'];
        $this->assertArrayHasKey('!=', $ruleArray);
        $this->assertEquals([['var' => 'status'], 'inactive'], $ruleArray['!=']);

        $source = ['status' => 'active'];
        $jsonLogicResult = $this->evaluator->evaluate($ruleArray, $source);
        $this->assertTrue($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertEmpty($cakeResult);

        $source = ['status' => 'inactive'];
        $jsonLogicResult = $this->evaluator->evaluate($ruleArray, $source);
        $this->assertFalse($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertNotEmpty($cakeResult);
    }

    /**
     * Test sameAs rule conversion (field comparison)
     *
     * @return void
     */
    public function testSameAs(): void
    {
        $validator = new Validator();
        $validator->sameAs('password_confirm', 'password');

        $result = $this->converter->convertValidator($validator);
        $passwordRule = $result['password_confirm'];

        $ruleArray = $passwordRule['rules'][0]['rule'];
        $this->assertArrayHasKey('===', $ruleArray);
        $this->assertEquals([['var' => 'password_confirm'], ['var' => 'password']], $ruleArray['===']);

        $source = ['password' => 'secret123', 'password_confirm' => 'secret123'];
        $jsonLogicResult = $this->evaluator->evaluate($ruleArray, $source);
        $this->assertTrue($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertEmpty($cakeResult);

        $source = ['password' => 'secret123', 'password_confirm' => 'different'];
        $jsonLogicResult = $this->evaluator->evaluate($ruleArray, $source);
        $this->assertFalse($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertNotEmpty($cakeResult);
    }

    /**
     * Test notSameAs rule conversion (field comparison)
     *
     * @return void
     */
    public function testNotSameAs(): void
    {
        $validator = new Validator();
        $validator->notSameAs('new_password', 'old_password');

        $result = $this->converter->convertValidator($validator);
        $passwordRule = $result['new_password'];

        $ruleArray = $passwordRule['rules'][0]['rule'];
        $this->assertArrayHasKey('!==', $ruleArray);
        $this->assertEquals([['var' => 'new_password'], ['var' => 'old_password']], $ruleArray['!==']);

        $source = ['new_password' => 'newpass123', 'old_password' => 'oldpass456'];
        $jsonLogicResult = $this->evaluator->evaluate($ruleArray, $source);
        $this->assertTrue($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertEmpty($cakeResult);

        $source = ['new_password' => 'samepass', 'old_password' => 'samepass'];
        $jsonLogicResult = $this->evaluator->evaluate($ruleArray, $source);
        $this->assertFalse($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertNotEmpty($cakeResult);
    }

    /**
     * Test notBlank rule conversion
     *
     * @return void
     */
    public function testNotBlank(): void
    {
        $validator = new Validator();
        $validator->notBlank('name');

        $result = $this->converter->convertValidator($validator);
        $nameRule = $result['name'];

        $ruleArray = $nameRule['rules'][0]['rule'];
        $this->assertArrayHasKey('!!', $ruleArray);
        $this->assertEquals(['var' => 'name'], $ruleArray['!!']);

        $source = ['name' => 'John Doe'];
        $jsonLogicResult = $this->evaluator->evaluate($ruleArray, $source);
        $this->assertTrue($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertEmpty($cakeResult);

        $source = ['name' => ''];
        $jsonLogicResult = $this->evaluator->evaluate($ruleArray, $source);
        $this->assertFalse($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertNotEmpty($cakeResult);

        $source = ['name' => '   '];
        $jsonLogicResult = $this->evaluator->evaluate($ruleArray, $source);

        $this->assertTrue($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertNotEmpty($cakeResult);
    }

    /**
     * Test notEmpty rule conversion
     *
     * @return void
     */
    public function testNotEmpty(): void
    {
        $validator = new Validator();
        $validator->notBlank('description');

        $result = $this->converter->convertValidator($validator);
        $descRule = $result['description'];

        $ruleArray = $descRule['rules'][0]['rule'];
        $this->assertArrayHasKey('!!', $ruleArray);
        $this->assertEquals(['var' => 'description'], $ruleArray['!!']);

        $source = ['description' => 'Some description text'];
        $jsonLogicResult = $this->evaluator->evaluate($ruleArray, $source);
        $this->assertTrue($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertEmpty($cakeResult);

        $source = ['description' => ''];
        $jsonLogicResult = $this->evaluator->evaluate($ruleArray, $source);
        $this->assertFalse($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertNotEmpty($cakeResult);
    }

    /**
     * Test greaterThan rule conversion
     *
     * @return void
     */
    public function testGreaterThan(): void
    {
        $validator = new Validator();
        $validator->greaterThan('age', 18);

        $result = $this->converter->convertValidator($validator);
        $ageRule = $result['age'];

        $ruleArray = $ageRule['rules'][0]['rule'];
        $this->assertArrayHasKey('>', $ruleArray);
        $this->assertEquals([['var' => 'age'], 18], $ruleArray['>']);

        $source = ['age' => 25];
        $jsonLogicResult = $this->evaluator->evaluate($ruleArray, $source);
        $this->assertTrue($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertEmpty($cakeResult);

        $source = ['age' => 15];
        $jsonLogicResult = $this->evaluator->evaluate($ruleArray, $source);
        $this->assertFalse($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertNotEmpty($cakeResult);
    }

    /**
     * Test greaterThanOrEqual rule conversion
     *
     * @return void
     */
    public function testGreaterThanOrEqual(): void
    {
        $validator = new Validator();
        $validator->greaterThanOrEqual('age', 18);

        $result = $this->converter->convertValidator($validator);
        $ageRule = $result['age'];

        $ruleArray = $ageRule['rules'][0]['rule'];
        $this->assertArrayHasKey('>=', $ruleArray);
        $this->assertEquals([['var' => 'age'], 18], $ruleArray['>=']);

        $source = ['age' => 18];
        $jsonLogicResult = $this->evaluator->evaluate($ruleArray, $source);
        $this->assertTrue($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertEmpty($cakeResult);

        $source = ['age' => 25];
        $jsonLogicResult = $this->evaluator->evaluate($ruleArray, $source);
        $this->assertTrue($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertEmpty($cakeResult);

        $source = ['age' => 15];
        $jsonLogicResult = $this->evaluator->evaluate($ruleArray, $source);
        $this->assertFalse($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertNotEmpty($cakeResult);
    }

    /**
     * Test lessThan rule conversion
     *
     * @return void
     */
    public function testLessThan(): void
    {
        $validator = new Validator();
        $validator->lessThan('age', 65);

        $result = $this->converter->convertValidator($validator);
        $ageRule = $result['age'];

        $ruleArray = $ageRule['rules'][0]['rule'];
        $this->assertArrayHasKey('<', $ruleArray);
        $this->assertEquals([['var' => 'age'], 65], $ruleArray['<']);

        $source = ['age' => 25];
        $jsonLogicResult = $this->evaluator->evaluate($ruleArray, $source);
        $this->assertTrue($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertEmpty($cakeResult);

        $source = ['age' => 70];
        $jsonLogicResult = $this->evaluator->evaluate($ruleArray, $source);
        $this->assertFalse($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertNotEmpty($cakeResult);
    }

    /**
     * Test lessThanOrEqual rule conversion
     *
     * @return void
     */
    public function testLessThanOrEqual(): void
    {
        $validator = new Validator();
        $validator->lessThanOrEqual('age', 65);

        $result = $this->converter->convertValidator($validator);
        $ageRule = $result['age'];

        $ruleArray = $ageRule['rules'][0]['rule'];
        $this->assertArrayHasKey('<=', $ruleArray);
        $this->assertEquals([['var' => 'age'], 65], $ruleArray['<=']);

        $source = ['age' => 65];
        $jsonLogicResult = $this->evaluator->evaluate($ruleArray, $source);
        $this->assertTrue($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertEmpty($cakeResult);

        $source = ['age' => 25];
        $jsonLogicResult = $this->evaluator->evaluate($ruleArray, $source);
        $this->assertTrue($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertEmpty($cakeResult);

        $source = ['age' => 70];
        $jsonLogicResult = $this->evaluator->evaluate($ruleArray, $source);
        $this->assertFalse($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertNotEmpty($cakeResult);
    }

    /**
     * Test lengthBetween converter
     *
     * @return void
     */
    public function testLengthBetween(): void
    {
        $validator = new Validator();
        $validator->lengthBetween('name', [2, 50]);

        $result = $this->converter->convertValidator($validator);

        $this->assertCount(1, $result);
        $this->assertArrayHasKey('name', $result);

        $expected = [
            '<=' => [2, ['length' => ['var' => 'name']], 50],
        ];

        $this->assertEquals($expected, $result['name']['rules'][0]['rule']);

        $source = ['name' => 'John Doe'];
        $jsonLogicResult = $this->evaluator->evaluate($result['name']['rules'][0]['rule'], $source);
        $this->assertTrue($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertEmpty($cakeResult);

        $source = ['name' => 'J'];
        $jsonLogicResult = $this->evaluator->evaluate($result['name']['rules'][0]['rule'], $source);
        $this->assertFalse($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertNotEmpty($cakeResult);

        $source = ['name' => str_repeat('A', 51)];
        $jsonLogicResult = $this->evaluator->evaluate($result['name']['rules'][0]['rule'], $source);
        $this->assertFalse($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertNotEmpty($cakeResult);
    }

    /**
     * Test range converter
     *
     * @return void
     */
    public function testRange(): void
    {
        $validator = new Validator();
        $validator->range('age', [18, 65]);

        $result = $this->converter->convertValidator($validator);

        $this->assertCount(1, $result);
        $this->assertArrayHasKey('age', $result);

        $expected = [
            '<=' => [18, ['var' => 'age'], 65],
        ];

        $this->assertEquals($expected, $result['age']['rules'][0]['rule']);

        $source = ['age' => 25];
        $jsonLogicResult = $this->evaluator->evaluate($result['age']['rules'][0]['rule'], $source);
        $this->assertTrue($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertEmpty($cakeResult);

        $source = ['age' => 18];
        $jsonLogicResult = $this->evaluator->evaluate($result['age']['rules'][0]['rule'], $source);
        $this->assertTrue($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertEmpty($cakeResult);

        $source = ['age' => 65];
        $jsonLogicResult = $this->evaluator->evaluate($result['age']['rules'][0]['rule'], $source);
        $this->assertTrue($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertEmpty($cakeResult);

        $source = ['age' => 15];
        $jsonLogicResult = $this->evaluator->evaluate($result['age']['rules'][0]['rule'], $source);
        $this->assertFalse($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertNotEmpty($cakeResult);

        $source = ['age' => 70];
        $jsonLogicResult = $this->evaluator->evaluate($result['age']['rules'][0]['rule'], $source);
        $this->assertFalse($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertNotEmpty($cakeResult);
    }

    /**
     * Test inList rule conversion
     *
     * @return void
     */
    public function testInList(): void
    {
        $validator = new Validator();
        $validator->inList('category', ['news', 'blog', 'page']);

        $result = $this->converter->convertValidator($validator);
        $categoryRule = $result['category'];

        $ruleArray = $categoryRule['rules'][0]['rule'];
        $this->assertArrayHasKey('in', $ruleArray);
        $this->assertEquals([['var' => 'category'], ['news', 'blog', 'page']], $ruleArray['in']);

        $source = ['category' => 'news'];
        $jsonLogicResult = $this->evaluator->evaluate($ruleArray, $source);
        $this->assertTrue($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertEmpty($cakeResult);

        $source = ['category' => 'blog'];
        $jsonLogicResult = $this->evaluator->evaluate($ruleArray, $source);
        $this->assertTrue($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertEmpty($cakeResult);

        $source = ['category' => 'invalid'];
        $jsonLogicResult = $this->evaluator->evaluate($ruleArray, $source);
        $this->assertFalse($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertNotEmpty($cakeResult);
    }

    /**
     * Test minLength rule conversion
     *
     * @return void
     */
    public function testMinLength(): void
    {
        $validator = new Validator();
        $validator->minLength('password', 8);

        $result = $this->converter->convertValidator($validator);
        $passwordRule = $result['password'];

        $ruleArray = $passwordRule['rules'][0]['rule'];
        $this->assertArrayHasKey('>=', $ruleArray);
        $this->assertEquals([['length' => ['var' => 'password']], 8], $ruleArray['>=']);

        $source = ['password' => 'password123'];
        $jsonLogicResult = $this->evaluator->evaluate($ruleArray, $source);
        $this->assertTrue($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertEmpty($cakeResult);

        $source = ['password' => 'password'];
        $jsonLogicResult = $this->evaluator->evaluate($ruleArray, $source);
        $this->assertTrue($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertEmpty($cakeResult);

        $source = ['password' => 'short'];
        $jsonLogicResult = $this->evaluator->evaluate($ruleArray, $source);
        $this->assertFalse($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertNotEmpty($cakeResult);
    }

    /**
     * Test maxLength rule conversion
     *
     * @return void
     */
    public function testMaxLength(): void
    {
        $validator = new Validator();
        $validator->maxLength('title', 255);

        $result = $this->converter->convertValidator($validator);
        $titleRule = $result['title'];

        $ruleArray = $titleRule['rules'][0]['rule'];
        $this->assertArrayHasKey('<=', $ruleArray);
        $this->assertEquals([['length' => ['var' => 'title']], 255], $ruleArray['<=']);

        $source = ['title' => 'Short Title'];
        $jsonLogicResult = $this->evaluator->evaluate($ruleArray, $source);
        $this->assertTrue($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertEmpty($cakeResult);

        $source = ['title' => str_repeat('A', 255)];
        $jsonLogicResult = $this->evaluator->evaluate($ruleArray, $source);
        $this->assertTrue($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertEmpty($cakeResult);

        $source = ['title' => str_repeat('A', 256)];
        $jsonLogicResult = $this->evaluator->evaluate($ruleArray, $source);
        $this->assertFalse($jsonLogicResult);

        $cakeResult = $validator->validate($source);
        $this->assertNotEmpty($cakeResult);
    }

    /**
     * Test getSupportedRules method
     *
     * @return void
     */
    public function testGetSupportedRules(): void
    {
        $supportedRules = $this->converter->getSupportedRules();

        $this->assertIsArray($supportedRules);
        $this->assertContains('equals', $supportedRules);
        $this->assertContains('greaterThan', $supportedRules);
        $this->assertContains('lessThan', $supportedRules);
        $this->assertContains('notBlank', $supportedRules);
        $this->assertContains('lengthBetween', $supportedRules);
        $this->assertContains('inList', $supportedRules);
    }

    /**
     * Test isRuleSupported method
     *
     * @return void
     */
    public function testIsRuleSupported(): void
    {
        $this->assertTrue($this->converter->isRuleSupported('equals'));
        $this->assertTrue($this->converter->isRuleSupported('greaterThan'));
        $this->assertTrue($this->converter->isRuleSupported('notBlank'));

        $this->assertFalse($this->converter->isRuleSupported('customRule'));
        $this->assertFalse($this->converter->isRuleSupported('nonExistent'));
    }

    /**
     * Test addRuleMapping method
     *
     * @return void
     */
    public function testAddRuleMapping(): void
    {
        $this->assertFalse($this->converter->isRuleSupported('customRule'));

        $this->converter->addRuleMapping('customRule', 'convertEquals');

        $this->assertTrue($this->converter->isRuleSupported('customRule'));
        $this->assertContains('customRule', $this->converter->getSupportedRules());
    }

    /**
     * Test complex validation scenario
     *
     * @return void
     */
    public function testComplexValidationScenario(): void
    {
        $validator = new Validator();
        $validator
            ->notBlank('name')
            ->lengthBetween('name', [2, 50])
            ->greaterThanOrEqual('age', 18)
            ->lessThan('age', 120)
            ->inList('status', ['active', 'inactive', 'pending'])
            ->sameAs('password_confirm', 'password')
            ->minLength('password', 8);

        $result = $this->converter->convertValidator($validator);

        $this->assertCount(5, $result);

        $nameRule = $result['name'];
        $nameArray = $nameRule['rules'];
        $this->assertCount(2, $nameArray);

        $ageRule = $result['age'];
        $ageArray = $ageRule['rules'];
        $this->assertCount(2, $ageArray);

        $statusRule = $result['status'];
        $statusArray = $statusRule['rules'];
        $this->assertCount(1, $statusArray);

        $passwordConfirmRule = $result['password_confirm'];
        $passwordConfirmArray = $passwordConfirmRule['rules'];
        $this->assertCount(1, $passwordConfirmArray);

        $passwordRule = $result['password'];
        $passwordArray = $passwordRule['rules'];
        $this->assertCount(1, $passwordArray);
    }

    /**
     * Test hasAtLeast converter using JsonLogic reduce
     * NOTE: This is a real CakePHP validation method
     *
     * @return void
     */
    public function testConvertHasAtLeast(): void
    {
        $rule = $this->converter->convertValidationRule('tags', 'hasAtLeast', new ValidationRule(['rule' => 'hasAtLeast', 'pass' => [3]]));

        $this->assertNotNull($rule);
        $ruleArray = $rule->toArray();
        $this->assertArrayHasKey('>=', $ruleArray);

        $validData = ['tags' => ['php', 'javascript', 'python', 'java']];
        $result = $this->evaluator->evaluate($ruleArray, $validData);
        $this->assertTrue($result, 'Array with 4 elements should pass hasAtLeast(3)');

        $invalidData = ['tags' => ['php', 'javascript']];
        $result = $this->evaluator->evaluate($ruleArray, $invalidData);
        $this->assertFalse($result, 'Array with 2 elements should fail hasAtLeast(3)');

        $edgeData = ['tags' => ['php', 'javascript', 'python']];
        $result = $this->evaluator->evaluate($ruleArray, $edgeData);
        $this->assertTrue($result, 'Array with exactly 3 elements should pass hasAtLeast(3)');
    }

    /**
     * Test hasAtMost converter using JsonLogic operations
     * NOTE: This is a real CakePHP validation method
     *
     * @return void
     */
    public function testConvertHasAtMost(): void
    {
        $rule = $this->converter->convertValidationRule('tags', 'hasAtMost', new ValidationRule(['rule' => 'hasAtMost', 'pass' => [5]]));

        $this->assertNotNull($rule);
        $ruleArray = $rule->toArray();
        $this->assertArrayHasKey('<=', $ruleArray);

        $validData = ['tags' => ['php', 'javascript', 'python']];
        $result = $this->evaluator->evaluate($ruleArray, $validData);
        $this->assertTrue($result, 'Array with 3 elements should pass hasAtMost(5)');

        $invalidData = ['tags' => ['php', 'javascript', 'python', 'java', 'c++', 'ruby', 'go']];
        $result = $this->evaluator->evaluate($ruleArray, $invalidData);
        $this->assertFalse($result, 'Array with 7 elements should fail hasAtMost(5)');
    }

    /**
     * Test multipleOptions converter using JsonLogic filter
     * NOTE: This is a real CakePHP validation method
     *
     * @return void
     */
    public function testConvertMultipleOptions(): void
    {
        $options = ['red', 'green', 'blue', 'yellow'];
        $rule = $this->converter->convertValidationRule('colors', 'multipleOptions', new ValidationRule(['rule' => 'multipleOptions', 'pass' => [$options]]));

        $this->assertNotNull($rule);
        $ruleArray = $rule->toArray();
        $this->assertArrayHasKey('==', $ruleArray);

        $validData = ['colors' => ['red', 'blue']];
        $result = $this->evaluator->evaluate($ruleArray, $validData);
        $this->assertTrue($result, 'Valid color selection should pass');

        $invalidData = ['colors' => ['red', 'purple']];
        $result = $this->evaluator->evaluate($ruleArray, $invalidData);
        $this->assertFalse($result, 'Invalid color selection should fail');

        $emptyData = ['colors' => []];
        $result = $this->evaluator->evaluate($ruleArray, $emptyData);
        $this->assertTrue($result, 'Empty selection should pass');
    }

    /**
     * Test notInList converter
     *
     * @deprecated This is not a real CakePHP validation method - @todo remove
     * @return void
     */
    public function testConvertNotInList(): void
    {
        $forbiddenValues = ['admin', 'root', 'system'];
        $rule = $this->converter->convertValidationRule('username', 'notInList', new ValidationRule(['rule' => 'notInList', 'pass' => [$forbiddenValues]]));
        $this->assertNotNull($rule);
        $ruleArray = $rule->toArray();
        $this->assertArrayHasKey('!', $ruleArray);

        $validData = ['username' => 'john_doe'];
        $result = $this->evaluator->evaluate($ruleArray, $validData);
        $this->assertTrue($result, 'Allowed username should pass');

        $invalidData = ['username' => 'admin'];
        $result = $this->evaluator->evaluate($ruleArray, $invalidData);
        $this->assertFalse($result, 'Forbidden username should fail');
    }

    /**
     * Test between converter
     * NOTE: This is not a standard CakePHP validation method but could be custom
     *
     * @return void
     */
    public function testConvertBetween(): void
    {
        $rule = $this->converter->convertValidationRule('score', 'between', new ValidationRule(['rule' => 'between', 'pass' => [1, 10]]));

        $this->assertNotNull($rule);
        $ruleArray = $rule->toArray();

        $validData = ['score' => 5];
        $result = $this->evaluator->evaluate($ruleArray, $validData);
        $this->assertTrue($result, 'Value 5 should be between 1 and 10');

        $belowData = ['score' => 0];
        $result = $this->evaluator->evaluate($ruleArray, $belowData);
        $this->assertFalse($result, 'Value 0 should not be between 1 and 10');

        $aboveData = ['score' => 11];
        $result = $this->evaluator->evaluate($ruleArray, $aboveData);
        $this->assertFalse($result, 'Value 11 should not be between 1 and 10');
    }

    /**
     * Test message handling in converted rules
     *
     * @return void
     */
    public function testMessageHandling(): void
    {
        $validator = new Validator();

        $validator->add('age', 'greaterThan', [
            'rule' => ['comparison', '>', 18],
            'message' => 'You must be at least 18 years old',
        ]);

        $validator->notBlank('name');
        $validator->lengthBetween('password', [8, 255], 'Password must be between 8 and 255 characters');
        $result = $this->converter->convertValidator($validator);

        $this->assertArrayHasKey('age', $result);
        $ageRules = $result['age']['rules'];
        $this->assertCount(1, $ageRules);
        $this->assertEquals('You must be at least 18 years old', $ageRules[0]['message']);

        $this->assertArrayHasKey('name', $result);
        $nameRules = $result['name']['rules'];
        $this->assertCount(1, $nameRules);
        $this->assertEquals('This field cannot be left empty', $nameRules[0]['message']);

        $this->assertArrayHasKey('password', $result);
        $passwordRules = $result['password']['rules'];
        $this->assertCount(1, $passwordRules);
        $this->assertEquals('Password must be between 8 and 255 characters', $passwordRules[0]['message']);
    }

    /**
     * Test multiple rules with different messages
     *
     * @return void
     */
    public function testMultipleRulesWithMessages(): void
    {
        $validator = new Validator();
        $validator
            ->add('username', 'notBlank', [
                'rule' => 'notBlank',
                'message' => 'Username is required',
            ])
            ->add('username', 'minLength', [
                'rule' => ['minLength', 3],
                'message' => 'Username must be at least 3 characters',
            ])
            ->add('username', 'maxLength', [
                'rule' => ['maxLength', 20],
                'message' => 'Username cannot exceed 20 characters',
            ]);

        $result = $this->converter->convertValidator($validator);

        $this->assertArrayHasKey('username', $result);
        $usernameRules = $result['username']['rules'];
        $this->assertCount(3, $usernameRules);

        $messages = array_column($usernameRules, 'message');
        $this->assertContains('Username is required', $messages);
        $this->assertContains('Username must be at least 3 characters', $messages);
        $this->assertContains('Username cannot exceed 20 characters', $messages);

        foreach ($usernameRules as $ruleData) {
            $this->assertArrayHasKey('rule', $ruleData);
            $this->assertArrayHasKey('message', $ruleData);
            $this->assertIsArray($ruleData['rule']);
            $this->assertIsString($ruleData['message']);
            $this->assertNotEmpty($ruleData['message']);
        }
    }

    /**
     * Test validation set with multiple rules for single field
     *
     * @return void
     */
    public function testValidationSetMultipleRulesOneField(): void
    {
        $validator = new Validator();
        $validator
            ->notBlank('username')
            ->minLength('username', 3)
            ->maxLength('username', 20)
            ->add('username', 'alphaNumeric', [
                'rule' => ['comparison', '!=', ''],
                'message' => 'Username must not be empty',
            ]);

        $result = $this->converter->convertValidator($validator);

        $this->assertArrayHasKey('username', $result);
        $this->assertArrayHasKey('rules', $result['username']);

        $usernameRules = $result['username']['rules'];
        $this->assertCount(4, $usernameRules);

        foreach ($usernameRules as $index => $ruleData) {
            $this->assertArrayHasKey('rule', $ruleData, "Rule {$index} missing 'rule' key");
            $this->assertArrayHasKey('message', $ruleData, "Rule {$index} missing 'message' key");
            $this->assertIsArray($ruleData['rule'], "Rule {$index} 'rule' should be array");
            $this->assertIsString($ruleData['message'], "Rule {$index} 'message' should be string");
            $this->assertNotEmpty($ruleData['message'], "Rule {$index} message should not be empty");
        }

        $validData = ['username' => 'john123'];
        $cakeErrors = $validator->validate($validData);
        $this->assertEmpty($cakeErrors, 'Valid data should pass CakePHP validation');

        $invalidData = ['username' => 'jo'];
        $cakeErrors = $validator->validate($invalidData);
        $this->assertNotEmpty($cakeErrors, 'Invalid data should fail CakePHP validation');
        $this->assertArrayHasKey('username', $cakeErrors);

        $invalidData = ['username' => str_repeat('a', 25)];
        $cakeErrors = $validator->validate($invalidData);
        $this->assertNotEmpty($cakeErrors, 'Invalid data should fail CakePHP validation');
        $this->assertArrayHasKey('username', $cakeErrors);

        $emptyData = ['username' => ''];
        $cakeErrors = $validator->validate($emptyData);
        $this->assertNotEmpty($cakeErrors, 'Empty data should fail CakePHP validation');
        $this->assertArrayHasKey('username', $cakeErrors);
    }

    /**
     * Test validation sets with multiple rules for multiple fields
     *
     * @return void
     */
    public function testValidationSetsMultipleRulesMultipleFields(): void
    {
        $validator = new Validator();
        $validator

            ->notBlank('username')
            ->minLength('username', 3)
            ->maxLength('username', 15)

            ->notBlank('email')
            ->add('email', 'contains', [
                'rule' => ['comparison', '!=', ''],
                'message' => 'Email is required',
            ])

            ->add('age', 'minimum', [
                'rule' => ['comparison', '>=', 18],
                'message' => 'Must be at least 18',
            ])
            ->add('age', 'maximum', [
                'rule' => ['comparison', '<=', 120],
                'message' => 'Must be under 120',
            ])

            ->inList('status', ['active', 'inactive', 'pending'])
            ->notBlank('status');

        $result = $this->converter->convertValidator($validator);

        $this->assertArrayHasKey('username', $result);
        $this->assertArrayHasKey('email', $result);
        $this->assertArrayHasKey('age', $result);
        $this->assertArrayHasKey('status', $result);

        $usernameRules = $result['username']['rules'];
        $this->assertCount(3, $usernameRules);

        $emailRules = $result['email']['rules'];
        $this->assertCount(2, $emailRules);

        $ageRules = $result['age']['rules'];
        $this->assertCount(2, $ageRules);

        $statusRules = $result['status']['rules'];
        $this->assertCount(2, $statusRules);

        foreach ($result as $fieldData) {
            $this->assertArrayHasKey('rules', $fieldData);
            $this->assertIsArray($fieldData['rules']);
            $this->assertNotEmpty($fieldData['rules']);

            foreach ($fieldData['rules'] as $ruleData) {
                $this->assertArrayHasKey('rule', $ruleData);
                $this->assertArrayHasKey('message', $ruleData);
                $this->assertIsArray($ruleData['rule']);
                $this->assertIsString($ruleData['message']);
            }
        }

        $validData = [
            'username' => 'john123',
            'email' => 'john@example.com',
            'age' => 25,
            'status' => 'active',
        ];
        $cakeErrors = $validator->validate($validData);
        $this->assertEmpty($cakeErrors, 'Valid data should pass CakePHP validation');

        $invalidData = [
            'username' => 'jo',
            'email' => '',
            'age' => 15,
            'status' => 'invalid',
        ];
        $cakeErrors = $validator->validate($invalidData);
        $this->assertNotEmpty($cakeErrors, 'Invalid data should fail CakePHP validation');
        $this->assertArrayHasKey('username', $cakeErrors);
        $this->assertArrayHasKey('email', $cakeErrors);
        $this->assertArrayHasKey('age', $cakeErrors);
        $this->assertArrayHasKey('status', $cakeErrors);
    }

    /**
     * Test validation set with complex rule combinations
     *
     * @return void
     */
    public function testValidationSetComplexRuleCombinations(): void
    {
        $validator = new Validator();
        $validator

            ->notBlank('password')
            ->minLength('password', 8)
            ->maxLength('password', 50)
            ->add('password', 'hasNumber', [
                'rule' => ['comparison', '!=', ''],
                'message' => 'Password must contain numbers',
            ])

            ->notBlank('password_confirm')
            ->sameAs('password_confirm', 'password')

            ->add('score', 'minimum', [
                'rule' => ['comparison', '>=', 0],
                'message' => 'Score cannot be negative',
            ])
            ->add('score', 'maximum', [
                'rule' => ['comparison', '<=', 100],
                'message' => 'Score cannot exceed 100',
            ])
            ->add('score', 'required', [
                'rule' => ['comparison', '!=', ''],
                'message' => 'Score is required',
            ]);

        $result = $this->converter->convertValidator($validator);

        $this->assertArrayHasKey('password', $result);
        $this->assertArrayHasKey('password_confirm', $result);
        $this->assertArrayHasKey('score', $result);

        $this->assertCount(4, $result['password']['rules']);
        $this->assertCount(2, $result['password_confirm']['rules']);
        $this->assertCount(3, $result['score']['rules']);

        $validData = [
            'password' => 'password123',
            'password_confirm' => 'password123',
            'score' => 85,
        ];
        $cakeErrors = $validator->validate($validData);
        $this->assertEmpty($cakeErrors, 'Valid data should pass CakePHP validation');

        $mismatchData = [
            'password' => 'password123',
            'password_confirm' => 'different',
            'score' => 85,
        ];
        $cakeErrors = $validator->validate($mismatchData);
        $this->assertNotEmpty($cakeErrors, 'Mismatched passwords should fail validation');
        $this->assertArrayHasKey('password_confirm', $cakeErrors);

        $outOfRangeData = [
            'password' => 'password123',
            'password_confirm' => 'password123',
            'score' => 150,
        ];
        $cakeErrors = $validator->validate($outOfRangeData);
        $this->assertNotEmpty($cakeErrors, 'Out of range score should fail validation');
        $this->assertArrayHasKey('score', $cakeErrors);
    }

    /**
     * Test validation set with field comparison rules
     *
     * @return void
     */
    public function testValidationSetFieldComparisons(): void
    {
        $validator = new Validator();
        $validator

            ->notBlank('start_value')
            ->add('start_value', 'validValue', [
                'rule' => ['comparison', '!=', ''],
                'message' => 'Start value is required',
            ])

            ->notBlank('end_value')
            ->add('end_value', 'validValue', [
                'rule' => ['comparison', '!=', ''],
                'message' => 'End value is required',
            ])
            ->add('end_value', 'afterStart', [
                'rule' => ['compareFields', 'start_value', '>'],
                'message' => 'End value must be greater than start value',
            ])

            ->add('min_value', 'required', [
                'rule' => ['comparison', '!=', ''],
                'message' => 'Min value is required',
            ])
            ->add('max_value', 'required', [
                'rule' => ['comparison', '!=', ''],
                'message' => 'Max value is required',
            ])
            ->add('max_value', 'greaterThanMin', [
                'rule' => ['compareFields', 'min_value', '>'],
                'message' => 'Max value must be greater than min value',
            ]);

        $result = $this->converter->convertValidator($validator);

        $this->assertArrayHasKey('start_value', $result);
        $this->assertArrayHasKey('end_value', $result);
        $this->assertArrayHasKey('min_value', $result);
        $this->assertArrayHasKey('max_value', $result);

        $this->assertCount(2, $result['start_value']['rules']);
        $this->assertCount(3, $result['end_value']['rules']);
        $this->assertCount(1, $result['min_value']['rules']);
        $this->assertCount(2, $result['max_value']['rules']);

        $validData = [
            'start_value' => 10,
            'end_value' => 20,
            'min_value' => 5,
            'max_value' => 15,
        ];
        $cakeErrors = $validator->validate($validData);
        $this->assertEmpty($cakeErrors, 'Valid data should pass CakePHP validation');

        $invalidOrderData = [
            'start_value' => 20,
            'end_value' => 10,
            'min_value' => 5,
            'max_value' => 15,
        ];
        $cakeErrors = $validator->validate($invalidOrderData);
        $this->assertNotEmpty($cakeErrors, 'Invalid value order should fail validation');
        $this->assertArrayHasKey('end_value', $cakeErrors);

        $invalidMinMaxData = [
            'start_value' => 10,
            'end_value' => 20,
            'min_value' => 15,
            'max_value' => 5,
        ];
        $cakeErrors = $validator->validate($invalidMinMaxData);
        $this->assertNotEmpty($cakeErrors, 'Invalid min/max order should fail validation');
        $this->assertArrayHasKey('max_value', $cakeErrors);
    }

    /**
     * Test critical implementation issue: ValidationRule argument extraction
     * Tests both array-based rules and string-based rules
     *
     * @return void
     */
    public function testCriticalIssueArgumentExtraction(): void
    {
        $validator = new Validator();

        $validator->add('age', 'ageCheck', [
            'rule' => ['comparison', '>', 18],
            'message' => 'Must be over 18',
        ]);

        $validator->add('username', 'lengthCheck', [
            'rule' => ['lengthBetween', 3, 20],
            'message' => 'Username must be 3-20 characters',
        ]);

        $validator->notBlank('email');

        $result = $this->converter->convertValidator($validator);

        $this->assertArrayHasKey('age', $result);
        $this->assertArrayHasKey('username', $result);
        $this->assertArrayHasKey('email', $result);

        $ageRule = $result['age']['rules'][0]['rule'];
        $this->assertArrayHasKey('>', $ageRule);
        $this->assertEquals([['var' => 'age'], 18], $ageRule['>']);

        $usernameRule = $result['username']['rules'][0]['rule'];
        $this->assertArrayHasKey('<=', $usernameRule);

        $emailRule = $result['email']['rules'][0]['rule'];
        $this->assertArrayHasKey('!!', $emailRule);

        $validData = [
            'age' => 25,
            'username' => 'john123',
            'email' => 'john@example.com',
        ];
        $cakeErrors = $validator->validate($validData);
        $this->assertEmpty($cakeErrors, 'Valid data should pass with correct argument extraction');

        $invalidData = [
            'age' => 15,
            'username' => 'jo',
            'email' => '',
        ];
        $cakeErrors = $validator->validate($invalidData);
        $this->assertNotEmpty($cakeErrors, 'Invalid data should fail with correct argument extraction');
        $this->assertArrayHasKey('age', $cakeErrors);
        $this->assertArrayHasKey('username', $cakeErrors);
        $this->assertArrayHasKey('email', $cakeErrors);
    }

    /**
     * Test critical implementation issue: Range operation efficiency
     * Verifies that range operations use betweenInclusive instead of separate comparisons
     *
     * @return void
     */
    public function testCriticalIssueRangeOperationEfficiency(): void
    {
        $validator = new Validator();

        $validator->range('score', [0, 100]);

        $validator->lengthBetween('description', [10, 500]);

        $betweenRule = $this->converter->convertValidationRule('rating', 'between', new ValidationRule(['rule' => 'between', 'pass' => [1, 5]]));

        $result = $this->converter->convertValidator($validator);

        $this->assertArrayHasKey('score', $result);
        $this->assertArrayHasKey('description', $result);

        $scoreRule = $result['score']['rules'][0]['rule'];
        $this->assertArrayHasKey('<=', $scoreRule, 'Range should use betweenInclusive creating simplified structure');

        $descriptionRule = $result['description']['rules'][0]['rule'];
        $this->assertArrayHasKey('<=', $descriptionRule, 'LengthBetween should use betweenInclusive creating simplified structure');

        $this->assertNotNull($betweenRule, 'Between rule should be converted');
        $ratingRuleArray = $betweenRule->toArray();
        $this->assertArrayHasKey('<=', $ratingRuleArray, 'Between should use betweenInclusive creating simplified structure');

        $validData = [
            'score' => 75,
            'description' => 'This is a valid description with enough characters',
        ];
        $cakeErrors = $validator->validate($validData);
        $this->assertEmpty($cakeErrors, 'Valid data should pass with optimized range operations');

        $invalidData = [
            'score' => 150,
            'description' => 'short',
        ];
        $cakeErrors = $validator->validate($invalidData);
        $this->assertNotEmpty($cakeErrors, 'Invalid data should fail with optimized range operations');
        $this->assertArrayHasKey('score', $cakeErrors);
        $this->assertArrayHasKey('description', $cakeErrors);
    }

    /**
     * Test critical implementation issue: Complex rule argument handling
     * Tests edge cases in argument extraction and rule processing
     *
     * @return void
     */
    public function testCriticalIssueComplexArgumentHandling(): void
    {
        $validator = new Validator();

        $validator->add('simple', 'noArgs', [
            'rule' => ['comparison', '!=', ''],
            'message' => 'Simple rule with no extra args',
        ]);

        $validator->add('complex', 'multiArgs', [
            'rule' => ['comparison', '>=', 18],
            'message' => 'Complex rule with multiple args',
        ]);

        $validator->add('end_date', 'afterStart', [
            'rule' => ['compareFields', 'start_date', '>'],
            'message' => 'End date must be after start date',
        ]);

        $validator->add('status', 'validStatus', [
            'rule' => ['inList', ['active', 'inactive', 'pending']],
            'message' => 'Status must be valid',
        ]);

        $result = $this->converter->convertValidator($validator);

        $this->assertArrayHasKey('simple', $result);
        $this->assertArrayHasKey('complex', $result);
        $this->assertArrayHasKey('end_date', $result);
        $this->assertArrayHasKey('status', $result);

        foreach ($result as $fieldData) {
            $this->assertArrayHasKey('rules', $fieldData);
            foreach ($fieldData['rules'] as $ruleData) {
                $this->assertArrayHasKey('rule', $ruleData);
                $this->assertArrayHasKey('message', $ruleData);
                $this->assertIsArray($ruleData['rule']);
                $this->assertIsString($ruleData['message']);
            }
        }

        $validData = [
            'simple' => 'not empty',
            'complex' => 25,
            'start_date' => 10,
            'end_date' => 20,
            'status' => 'active',
        ];
        $cakeErrors = $validator->validate($validData);
        $this->assertEmpty($cakeErrors, 'Valid complex data should pass argument handling');

        $invalidData = [
            'simple' => '',
            'complex' => 15,
            'start_date' => 20,
            'end_date' => 10,
            'status' => 'invalid',
        ];
        $cakeErrors = $validator->validate($invalidData);
        $this->assertNotEmpty($cakeErrors, 'Invalid complex data should fail argument handling');
        $this->assertArrayHasKey('simple', $cakeErrors);
        $this->assertArrayHasKey('complex', $cakeErrors);
        $this->assertArrayHasKey('end_date', $cakeErrors);
        $this->assertArrayHasKey('status', $cakeErrors);
    }

    /**
     * Test unsupported rule handling
     *
     * @return void
     */
    public function testUnsupportedRuleHandling(): void
    {
        $validator = new Validator();
        $validator->add('field', 'customRule', [
            'rule' => 'customRule',
            'message' => 'Custom rule failed',
        ]);

        $result = $this->converter->convertValidator($validator);

        $this->assertArrayNotHasKey('field', $result);
    }

    /**
     * Test email rule throws exception
     *
     * @return void
     */
    public function testEmailRuleThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Email validation rules are not yet supported in JsonLogic conversion');

        $validator = new Validator();
        $validator->email('email');

        $this->converter->convertValidator($validator);
    }
}
