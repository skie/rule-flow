<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase;

use Cake\ORM\Entity;
use Cake\TestSuite\TestCase;
use RuleFlow\JsonLogicEvaluator;

/**
 * JsonLogicEvaluator Test Case
 */
class JsonLogicEvaluatorTest extends TestCase
{
    /**
     * @var \RuleFlow\JsonLogicEvaluator
     */
    protected $evaluator;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->evaluator = new JsonLogicEvaluator();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->evaluator);
        parent::tearDown();
    }

    /**
     * Test evaluate with empty rule
     *
     * @return void
     */
    public function testEvaluateEmptyRule(): void
    {
        $entity = new Entity(['name' => 'Test']);
        $this->assertFalse($this->evaluator->evaluate([], $entity));
        $this->assertFalse($this->evaluator->evaluate(null, $entity));
    }

    /**
     * Test evaluate with direct boolean value
     *
     * @return void
     */
    public function testEvaluateDirectBooleanValue(): void
    {
        $entity = new Entity(['name' => 'Test']);
        $this->assertTrue($this->evaluator->evaluate(['value' => true], $entity));
        $this->assertFalse($this->evaluator->evaluate(['value' => false], $entity));
    }

    /**
     * Test evaluate with var operator
     *
     * @return void
     */
    public function testEvaluateVarOperator(): void
    {
        $entity = new Entity([
            'name' => 'Test',
            'age' => 25,
            'address' => [
                'city' => 'New York',
                'zip' => '10001',
            ],
        ]);

        // Simple property access
        $rule = ['var' => 'name'];
        $this->assertEquals('Test', $this->evaluator->evaluate($rule, $entity));

        // Default value for missing property
        $rule = ['var' => ['missing', 'default']];
        $this->assertEquals('default', $this->evaluator->evaluate($rule, $entity));

        // Nested property access
        $rule = ['var' => 'address.city'];
        $this->assertEquals('New York', $this->evaluator->evaluate($rule, $entity));
    }

    /**
     * Test evaluate with logical operators
     *
     * @return void
     */
    public function testEvaluateLogicalOperators(): void
    {
        $entity = new Entity([
            'active' => true,
            'age' => 25,
        ]);

        // AND operator
        $rule = [
            'and' => [
                ['var' => 'active'],
                ['>=', [['var' => 'age'], 18]],
            ],
        ];
        $this->assertTrue($this->evaluator->evaluate($rule, $entity));

        $entity->set('active', false);
        $this->assertFalse($this->evaluator->evaluate($rule, $entity));

        // OR operator
        $rule = [
            'or' => [
                ['var' => 'active'],
                ['>=', [['var' => 'age'], 18]],
            ],
        ];
        $this->assertTrue($this->evaluator->evaluate($rule, $entity));

        $entity->set('age', 16);
        $this->assertFalse($this->evaluator->evaluate($rule, $entity));

        // NOT operator
        $rule = ['!' => ['var' => 'active']];
        $this->assertTrue($this->evaluator->evaluate($rule, $entity));

        // entity.active is false here, so NOT false should be true
        $entity->set('active', true);
        $this->assertFalse($this->evaluator->evaluate($rule, $entity));
    }

    /**
     * Test evaluate with comparison operators
     *
     * @return void
     */
    public function testEvaluateComparisonOperators(): void
    {
        $entity = new Entity([
            'name' => 'Test',
            'age' => 25,
            'code' => '25',
            'status' => 'active',
        ]);

        // Equality (with type coercion)
        $rule = ['==' => [['var' => 'age'], ['var' => 'code']]];
        $this->assertTrue($this->evaluator->evaluate($rule, $entity));

        // Strict equality (no type coercion)
        $rule = ['===' => [['var' => 'age'], ['var' => 'code']]];
        $this->assertFalse($this->evaluator->evaluate($rule, $entity));

        // Greater than
        $rule = ['>' => [['var' => 'age'], 18]];
        $this->assertTrue($this->evaluator->evaluate($rule, $entity));

        // Less than
        $rule = ['<' => [['var' => 'age'], 30]];
        $this->assertTrue($this->evaluator->evaluate($rule, $entity));

        // Greater than or equal
        $rule = ['>=' => [['var' => 'age'], 25]];
        $this->assertTrue($this->evaluator->evaluate($rule, $entity));

        // Less than or equal
        $rule = ['<=' => [['var' => 'age'], 25]];
        $this->assertTrue($this->evaluator->evaluate($rule, $entity));

        // Not equal
        $rule = ['!=' => [['var' => 'status'], 'inactive']];
        $this->assertTrue($this->evaluator->evaluate($rule, $entity));

        // Strict not equal
        $rule = ['!==' => [['var' => 'age'], ['var' => 'code']]];
        $this->assertTrue($this->evaluator->evaluate($rule, $entity));

        // Between (exclusive)
        $rule = ['>' => [10, ['var' => 'age'], 30]];
        $this->assertFalse($this->evaluator->evaluate($rule, $entity));
        $rule = ['>' => [30, ['var' => 'age'], 10]];
        $this->assertTrue($this->evaluator->evaluate($rule, $entity));

        // Between (inclusive)
        $rule = ['>=' => [10, ['var' => 'age'], 25]];
        $this->assertFalse($this->evaluator->evaluate($rule, $entity));
        $rule = ['>=' => [25, ['var' => 'age'], 10]];
        $this->assertTrue($this->evaluator->evaluate($rule, $entity));
    }

    /**
     * Test evaluate with string operators
     *
     * @return void
     */
    public function testEvaluateStringOperators(): void
    {
        $entity = new Entity([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // cat (concatenation)
        $rule = ['cat' => [['var' => 'name'], ' <', ['var' => 'email'], '>']];
        $this->assertEquals('Test User <test@example.com>', $this->evaluator->evaluate($rule, $entity));

        // substr
        $rule = ['substr' => [['var' => 'name'], 0, 4]];
        $this->assertEquals('Test', $this->evaluator->evaluate($rule, $entity));

        // contains
        $rule = ['contains' => [['var' => 'name'], 'User']];
        $this->assertTrue($this->evaluator->evaluate($rule, $entity));

        // startsWith
        $rule = ['startsWith' => [['var' => 'name'], 'Test']];
        $this->assertTrue($this->evaluator->evaluate($rule, $entity));

        // endsWith
        $rule = ['endsWith' => [['var' => 'name'], 'User']];
        $this->assertTrue($this->evaluator->evaluate($rule, $entity));
    }

    /**
     * Test evaluate with array operators
     *
     * @return void
     */
    public function testEvaluateArrayOperators(): void
    {
        $entity = new Entity([
            'numbers' => [1, 2, 3, 4, 5],
            'tags' => ['admin', 'user', 'editor'],
        ]);

        // in
        $rule = ['in' => ['admin', ['var' => 'tags']]];
        $this->assertTrue($this->evaluator->evaluate($rule, $entity));

        $rule = ['merge' => [['var' => 'numbers'], [6, 7]]];
        $result = $this->evaluator->getMergeResult($rule, $entity->toArray());
        $this->assertEquals([1, 2, 3, 4, 5, 6, 7], $result);

        $rule = ['reduce' => [
            ['var' => 'numbers'],
            ['+' => [['var' => 'accumulator'], ['var' => 'current']]],
            0,
        ]];
        $this->assertEquals(15, $this->evaluator->evaluate($rule, $entity));
    }

    /**
     * Test evaluate with math operators
     *
     * @return void
     */
    public function testEvaluateMathOperators(): void
    {
        $entity = new Entity([
            'x' => 10,
            'y' => 5,
            'z' => 2,
        ]);

        // Addition
        $rule = ['+' => [['var' => 'x'], ['var' => 'y'], ['var' => 'z']]];
        $this->assertEquals(17, $this->evaluator->evaluate($rule, $entity));

        // Subtraction
        $rule = ['-' => [['var' => 'x'], ['var' => 'y']]];
        $this->assertEquals(5, $this->evaluator->evaluate($rule, $entity));

        // Multiplication
        $rule = ['*' => [['var' => 'x'], ['var' => 'y']]];
        $this->assertEquals(50, $this->evaluator->evaluate($rule, $entity));

        // Division
        $rule = ['/' => [['var' => 'x'], ['var' => 'z']]];
        $this->assertEquals(5, $this->evaluator->evaluate($rule, $entity));

        // Modulo
        $rule = ['%' => [['var' => 'x'], ['var' => 'z']]];
        $this->assertEquals(0, $this->evaluator->evaluate($rule, $entity));

        // Min
        $rule = ['min' => [['var' => 'x'], ['var' => 'y'], ['var' => 'z']]];
        $this->assertEquals(2, $this->evaluator->evaluate($rule, $entity));

        // Max
        $rule = ['max' => [['var' => 'x'], ['var' => 'y'], ['var' => 'z']]];
        $this->assertEquals(10, $this->evaluator->evaluate($rule, $entity));
    }

    /**
     * Test evaluate with if operator
     *
     * @return void
     */
    public function testEvaluateIfOperator(): void
    {
        $entity = new Entity([
            'age' => 25,
        ]);

        // Simple if-then-else
        $rule = ['if' => [
            ['<' => [['var' => 'age'], 18]],
            'Minor',
            ['>=' => [['var' => 'age'], 18]],
            'Adult',
        ]];
        $this->assertEquals('Adult', $this->evaluator->evaluate($rule, $entity));

        // More complex if-then-elseif-else
        $rule = ['if' => [
            ['<' => [['var' => 'age'], 13]],
            'Child',
            ['<' => [['var' => 'age'], 20]],
            'Teenager',
            ['<' => [['var' => 'age'], 65]],
            'Adult',
            'Senior',
        ]];
        $this->assertEquals('Adult', $this->evaluator->evaluate($rule, $entity));
    }

    /**
     * Test evaluate with complex rules
     *
     * @return void
     */
    public function testEvaluateComplexRules(): void
    {
        $entity = new Entity([
            'user' => [
                'age' => 25,
                'role' => 'admin',
                'permissions' => ['read', 'write'],
            ],
            'settings' => [
                'darkMode' => true,
                'notifications' => [
                    'email' => true,
                    'sms' => false,
                ],
            ],
        ]);

        // Complex rule 1: User is admin AND has write permission AND is over 18
        $rule = [
            'and' => [
                ['==' => [['var' => 'user.role'], 'admin']],
                ['in' => ['write', ['var' => 'user.permissions']]],
                ['>' => [['var' => 'user.age'], 18]],
            ],
        ];
        $this->assertTrue($this->evaluator->evaluate($rule, $entity));

        // Complex rule 2: User is admin OR has specific permissions
        $rule = [
            'or' => [
                ['==' => [['var' => 'user.role'], 'admin']],
                [
                    'and' => [
                        ['in' => ['read', ['var' => 'user.permissions']]],
                        ['in' => ['write', ['var' => 'user.permissions']]],
                        ['>' => [['var' => 'user.age'], 30]],
                    ],
                ],
            ],
        ];
        $this->assertTrue($this->evaluator->evaluate($rule, $entity));

        // Make the second condition false
        $entity->set('user.age', 20);
        $this->assertTrue($this->evaluator->evaluate($rule, $entity));

        // Make both conditions false
        $entity->set('user.role', 'user');
        $this->assertFalse($this->evaluator->evaluate($rule, $entity));
    }

    /**
     * Test evaluate with map and filter operators
     *
     * @return void
     */
    public function testEvaluateMapAndFilterOperators(): void
    {
        $entity = new Entity([
            'numbers' => [1, 2, 3, 4, 5, 6],
            'people' => [
                ['name' => 'Alice', 'age' => 25, 'city' => 'New York', 'roles' => ['admin', 'user']],
                ['name' => 'Bob', 'age' => 17, 'city' => 'Boston', 'roles' => ['user']],
                ['name' => 'Charlie', 'age' => 30, 'city' => 'Chicago', 'roles' => ['manager', 'user']],
                ['name' => 'David', 'age' => 15, 'city' => 'Denver', 'roles' => ['guest']],
                ['name' => 'Eve', 'age' => 28, 'city' => 'New York', 'roles' => ['user', 'tester']],
            ],
        ]);

        // Map operation - Double each number
        $rule = ['map' => [
            ['var' => 'numbers'],
            ['*' => [['var' => '_'], 2]],
        ]];
        $result = $this->evaluator->evaluate($rule, $entity);
        $this->assertEquals([2, 4, 6, 8, 10, 12], $result);

        // Map with object access - Extract names
        $rule = ['map' => [
            ['var' => 'people'],
            ['var' => 'name'],
        ]];
        $result = $this->evaluator->evaluate($rule, $entity);
        $this->assertEquals(
            ['Alice', 'Bob', 'Charlie', 'David', 'Eve'],
            $result,
        );

        // Map with transformation - Create greeting messages
        $rule = ['map' => [
            ['var' => 'people'],
            ['cat' => [
                'Hello, ',
                ['var' => 'name'],
                ' from ',
                ['var' => 'city'],
                '!',
            ]],
        ]];
        $result = $this->evaluator->evaluate($rule, $entity);
        $this->assertEquals('Hello, Alice from New York!', $result[0]);
        $this->assertCount(5, $result);

        // Filter operation - Even numbers
        $rule = ['filter' => [
            ['var' => 'numbers'],
            ['==' => [['%' => [['var' => '_'], 2]], 0]],
        ]];
        $result = $this->evaluator->evaluate($rule, $entity);
        $this->assertEquals([2, 4, 6], $result);

        // Filter with complex condition - Adults in New York
        $rule = ['filter' => [
            ['var' => 'people'],
            ['and' => [
                ['>=' => [['var' => 'age'], 18]],
                ['==' => [['var' => 'city'], 'New York']],
            ]],
        ]];
        $result = $this->evaluator->evaluate($rule, $entity);
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('Alice', $result[0]['name']);
        $this->assertEquals('Eve', $result[1]['name']);

        // Filter with in operator - Find users with admin role
        $rule = ['filter' => [
            ['var' => 'people'],
            ['in' => ['admin', ['var' => 'roles']]],
        ]];
        $result = $this->evaluator->evaluate($rule, $entity);
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('Alice', $result[0]['name']);

        // Combining map and filter - Double age of adults
        $rule = ['map' => [
            ['filter' => [
                ['var' => 'people'],
                ['>=' => [['var' => 'age'], 18]],
            ]],
            ['*' => [['var' => 'age'], 2]],
        ]];
        $result = $this->evaluator->evaluate($rule, $entity);
        $this->assertEquals([50, 60, 56], $result);

        // Complex chaining - Names of people with user role
        $rule = ['map' => [
            ['filter' => [
                ['var' => 'people'],
                ['in' => ['user', ['var' => 'roles']]],
            ]],
            ['var' => 'name'],
        ]];
        $result = $this->evaluator->evaluate($rule, $entity);
        $this->assertEquals(
            ['Alice', 'Bob', 'Charlie', 'Eve'],
            $result,
        );
    }

    /**
     * Test evaluate with advanced array operations
     *
     * @return void
     */
    public function testEvaluateAdvancedArrayOperations(): void
    {
        $data = [
            'orders' => [
                ['amount' => 500],
                ['amount' => 600],
            ],
            'cart' => [
                'items' => [
                    ['in_stock' => true, 'price' => 300],
                    ['in_stock' => true, 'price' => 250],
                ],
            ],
            'answers' => [
                ['is_correct' => true],
                ['is_correct' => true],
                ['is_correct' => false],
                ['is_correct' => true],
                ['is_correct' => true],
                ['is_correct' => true],
                ['is_correct' => false],
            ],
            'users' => [
                ['name' => 'Alice', 'purchases' => [100, 200, 50]],
                ['name' => 'Bob', 'purchases' => [25, 30]],
                ['name' => 'Charlie', 'purchases' => [500, 100, 200]],
            ],
        ];
        $entity = new Entity($data);

        // Test reduce for summing values
        $rule = ['>=' => [
            ['reduce' => [
                ['var' => 'orders'],
                ['+' => [['var' => 'accumulator'], ['var' => 'current.amount']]],
                0,
            ]],
            1000,
        ]];
        $result = $this->evaluator->evaluate($rule, $entity);
        $this->assertTrue($result);

        // Test all operator for validating all items
        $rule = ['all' => [
            ['var' => 'cart.items'],
            ['var' => 'in_stock'],
        ]];
        $result = $this->evaluator->evaluate($rule, $entity);
        $this->assertTrue($result);

        $workData = $data;
        $workData['cart']['items'][1]['in_stock'] = false;
        $entity = new Entity($workData);
        $result = $this->evaluator->evaluate($rule, $entity);
        $this->assertFalse($result);

        $entity = new Entity($data);

        // Test reduce with complex condition - Count correct answers
        $rule = ['reduce' => [
            ['var' => 'answers'],
            ['+' => [
                ['var' => 'accumulator'],
                ['if' => [['var' => 'current.is_correct'], 1, 0]],
            ]],
            0,
        ]];
        $result = $this->evaluator->evaluate($rule, $entity);
        $this->assertEquals(5, $result);

        // Test combined operations - Test passed based on correct answers
        $rule = ['if' => [
            ['>' => [
                ['reduce' => [
                    ['var' => 'answers'],
                    ['+' => [
                        ['var' => 'accumulator'],
                        ['if' => [['var' => 'current.is_correct'], 1, 0]],
                    ]],
                    0,
                ]],
                4,
            ]],
            'passed',
            'failed',
        ]];
        $result = $this->evaluator->evaluate($rule, $entity);
        $this->assertEquals('passed', $result);

        // Test some + reduce - Check if any user has total purchases > 700
        $rule = ['some' => [
            ['var' => 'users'],
            ['>' => [
                ['reduce' => [
                    ['var' => 'purchases'], // Access current user's purchases (relative to user)
                    ['+' => [['var' => 'accumulator'], ['var' => 'current']]],
                    0,
                ]],
                700,
            ]],
        ]];

        $result = $this->evaluator->evaluate($rule, $entity);
        $this->assertTrue($result);
    }

    /**
     * Test form validation scenarios
     *
     * @return void
     */
    public function testFormValidationScenarios(): void
    {
        $entity = new Entity([
            'form' => [
                'fields' => [
                    ['name' => 'username', 'value' => 'johndoe'],
                    ['name' => 'email', 'value' => 'john@example.com'],
                    ['name' => 'password', 'value' => 'p@ssw0rd'],
                    ['name' => 'confirmPassword', 'value' => 'p@ssw0rd'],
                ],
            ],
            'password' => 'p@ssw0rd',
            'confirmPassword' => 'p@ssw0rd',
            'age' => 17,
            'country' => 'US',
        ]);

        // Test required fields validation
        $rule = ['all' => [
            ['var' => 'form.fields'],
            ['!!' => ['var' => 'value']],
        ]];
        $result = $this->evaluator->evaluate($rule, $entity);
        $this->assertTrue($result);

        // Test password matching
        $rule = ['==' => [
            ['var' => 'password'],
            ['var' => 'confirmPassword'],
        ]];
        $result = $this->evaluator->evaluate($rule, $entity);
        $this->assertTrue($result);

        // Test complex age validation with country-specific rules
        $rule = ['if' => [
            ['==' => [['var' => 'country'], 'US']],
            ['>=' => [['var' => 'age'], 21]], // US drinking age
            ['>=' => [['var' => 'age'], 18]], // Default age
        ]];
        $result = $this->evaluator->evaluate($rule, $entity);
        $this->assertFalse($result);

        // Update age and check again
        $entity->set('age', 22);
        $result = $this->evaluator->evaluate($rule, $entity);
        $this->assertTrue($result);

        // Complex validation combining multiple rules
        $rule = ['and' => [
            // All fields have values
            ['all' => [['var' => 'form.fields'], ['!!' => ['var' => 'value']]]],

            // Passwords match
            ['==' => [['var' => 'password'], ['var' => 'confirmPassword']]],

            // Age validation based on country
            ['if' => [
                ['==' => [['var' => 'country'], 'US']],
                ['>=' => [['var' => 'age'], 21]],
                ['>=' => [['var' => 'age'], 18]],
            ]],
        ]];
        $result = $this->evaluator->evaluate($rule, $entity);
        $this->assertTrue($result);

        // Test failure case
        $entity->set('age', 17);
        $result = $this->evaluator->evaluate($rule, $entity);
        $this->assertFalse($result);
    }

    /**
     * Test in and not in operators
     *
     * @return void
     */
    public function testInAndNotInOperators(): void
    {
        $entity = new Entity([
            'username' => 'admin',
            'role' => 'user',
            'tags' => ['php', 'javascript', 'python'],
            'permissions' => ['read', 'write'],
        ]);

        // Test basic in operator - should return true
        $rule = ['in' => ['admin', ['admin', 'root', 'system']]];
        $result = $this->evaluator->evaluate($rule, $entity);
        $this->assertTrue($result, 'admin should be in the list');

        // Test in operator with variable - should return true
        $rule = ['in' => [['var' => 'username'], ['admin', 'root', 'system']]];
        $result = $this->evaluator->evaluate($rule, $entity);
        $this->assertTrue($result, 'username admin should be in the list');

        // Test NOT in operator - should return false (admin IS in the list)
        $rule = ['!' => ['in' => [['var' => 'username'], ['admin', 'root', 'system']]]];
        $result = $this->evaluator->evaluate($rule, $entity);
        $this->assertFalse($result, 'NOT(admin in list) should be false');

        // Test NOT in operator with nested structure - the failing case
        $rule = ['!' => ['in' => [['var' => 'username'], ['admin', 'root', 'system']]]];
        $data = ['username' => 'admin'];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertFalse($result, 'NOT(admin in [admin,root,system]) should be false');

        // Test in operator with user not in list - should return false
        $rule = ['in' => [['var' => 'role'], ['admin', 'root', 'system']]];
        $result = $this->evaluator->evaluate($rule, $entity);
        $this->assertFalse($result, 'role user should not be in admin list');

        // Test NOT in operator with user not in list - should return true
        $rule = ['!' => ['in' => [['var' => 'role'], ['admin', 'root', 'system']]]];
        $result = $this->evaluator->evaluate($rule, $entity);
        $this->assertTrue($result, 'NOT(user in admin list) should be true');

        // Test in operator with array field
        $rule = ['in' => ['php', ['var' => 'tags']]];
        $result = $this->evaluator->evaluate($rule, $entity);
        $this->assertTrue($result, 'php should be in tags array');

        // Test in operator with string search
        $rule = ['in' => ['min', 'admin']];
        $result = $this->evaluator->evaluate($rule, $entity);
        $this->assertTrue($result, 'min should be found in admin string');
    }

    /**
     * Test the specific failing case from ValidationRuleConverter
     *
     * @return void
     */
    public function testNotInListValidationCase(): void
    {
        // This is the exact case that's failing in ValidationRuleConverter tests
        $rule = ['!' => ['in' => [['var' => 'username'], ['admin', 'root', 'system']]]];

        // Test with forbidden username - should return false (validation fails)
        $data = ['username' => 'admin'];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertFalse($result, 'admin username should fail notInList validation');

        // Test with allowed username - should return true (validation passes)
        $data = ['username' => 'johndoe'];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertTrue($result, 'johndoe username should pass notInList validation');

        // Test with empty username - should return true (not in forbidden list)
        $data = ['username' => ''];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertTrue($result, 'empty username should pass notInList validation');

        // Test with null username - should return true (not in forbidden list)
        $data = ['username' => null];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertTrue($result, 'null username should pass notInList validation');
    }

    /**
     * Test all operation with in operator for containsAll scenario
     *
     * @return void
     */
    public function testAllOperatorWithInForContainsAll(): void
    {
        // Test case: check if ['read', 'write', 'execute'] contains all of ['read', 'write']
        $data = ['permissions' => ['read', 'write', 'execute']];

        // This should check: for each item in ['read', 'write'], is it in permissions array?
        $rule = [
            'all' => [
                ['read', 'write'], // iterate over these required values
                ['in' => [['var' => ''], ['var' => 'permissions']]], // check if current item is in permissions
            ],
        ];

        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertTrue($result, 'All required permissions should be found in the permissions array');

        // Test failure case
        $data = ['permissions' => ['read']]; // missing 'write'
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertFalse($result, 'Should fail when not all required permissions are present');

        // Test with different structure - what our converter currently generates
        $wrongRule = [
            'all' => [
                ['read', 'write'], // iterate over these required values
                ['in' => [['var' => ''], ['var' => 'permissions']]], // this should work
            ],
        ];

        $data = ['permissions' => ['read', 'write', 'execute']];
        $result = $this->evaluator->evaluate($wrongRule, $data);
        $this->assertTrue($result, 'This should work - checking if each required value is in permissions');
    }

    /**
     * Test var operator according to JsonLogic specification
     *
     * @return void
     */
    public function testVarOperatorSpecification(): void
    {
        // Basic var access
        $rule = ['var' => 'a'];
        $data = ['a' => 1, 'b' => 2];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertEquals(1, $result, 'Basic var access should work');

        // Var with default value
        $rule = ['var' => ['z', 26]];
        $data = ['a' => 1, 'b' => 2];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertEquals(26, $result, 'Var with default should return default for missing key');

        // Dot notation access
        $rule = ['var' => 'champ.name'];
        $data = [
            'champ' => [
                'name' => 'Fezzig',
                'height' => 223,
            ],
            'challenger' => [
                'name' => 'Dread Pirate Roberts',
                'height' => 183,
            ],
        ];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertEquals('Fezzig', $result, 'Dot notation should work');

        // Array access by numeric index
        $rule = ['var' => 1];
        $data = ['zero', 'one', 'two'];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertEquals('one', $result, 'Array access by index should work');

        // Empty string var to get entire data object
        $rule = ['var' => ''];
        $data = ['name' => 'Dolly', 'age' => 25];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertEquals($data, $result, 'Empty string var should return entire data object');
    }

    /**
     * Test all, some, none operations according to JsonLogic specification
     *
     * @return void
     */
    public function testCollectionOperationsSpecification(): void
    {
        // Test all operation with simple array and var("")
        $rule = ['all' => [[1, 2, 3], ['>' => [['var' => ''], 0]]]];
        $data = [];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertTrue($result, 'All elements > 0 should be true');

        // Test all operation with one failing element
        $rule = ['all' => [[-1, 2, 3], ['>' => [['var' => ''], 0]]]];
        $data = [];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertFalse($result, 'All elements > 0 should be false when one is negative');

        // Test some operation with mixed array
        $rule = ['some' => [[-1, 0, 1], ['>' => [['var' => ''], 0]]]];
        $data = [];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertTrue($result, 'Some elements > 0 should be true');

        // Test some operation with all negative
        $rule = ['some' => [[-1, -2, -3], ['>' => [['var' => ''], 0]]]];
        $data = [];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertFalse($result, 'Some elements > 0 should be false when all are negative');

        // Test none operation with all negative
        $rule = ['none' => [[-3, -2, -1], ['>' => [['var' => ''], 0]]]];
        $data = [];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertTrue($result, 'None elements > 0 should be true when all are negative');

        // Test none operation with one positive
        $rule = ['none' => [[-3, -2, 1], ['>' => [['var' => ''], 0]]]];
        $data = [];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertFalse($result, 'None elements > 0 should be false when one is positive');

        // Test with object properties
        $rule = ['some' => [['var' => 'pies'], ['==' => [['var' => 'filling'], 'apple']]]];
        $data = [
            'pies' => [
                ['filling' => 'pumpkin', 'temp' => 110],
                ['filling' => 'rhubarb', 'temp' => 210],
                ['filling' => 'apple', 'temp' => 310],
            ],
        ];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertTrue($result, 'Some pies should have apple filling');

        // Test empty arrays.
        // skipped fails as we cant split arrays and object using arrays in php.
    }

    /**
     * Test map and filter operations with proper context handling
     *
     * @return void
     */
    public function testMapFilterWithContextHandling(): void
    {
        // Test map with var("") - should access current element
        $rule = ['map' => [['var' => 'integers'], ['*' => [['var' => ''], 2]]]];
        $data = ['integers' => [1, 2, 3, 4, 5]];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertEquals([2, 4, 6, 8, 10], $result, 'Map should double each element');

        // Test filter with var("") - should access current element
        $rule = ['filter' => [['var' => 'integers'], ['%' => [['var' => ''], 2]]]];
        $data = ['integers' => [1, 2, 3, 4, 5]];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertEquals([1, 3, 5], $result, 'Filter should return odd numbers');

        // Test map with object properties
        $rule = ['map' => [['var' => 'people'], ['var' => 'name']]];
        $data = [
            'people' => [
                ['name' => 'Alice', 'age' => 25],
                ['name' => 'Bob', 'age' => 30],
                ['name' => 'Charlie', 'age' => 35],
            ],
        ];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertEquals(['Alice', 'Bob', 'Charlie'], $result, 'Map should extract names');

        // Test filter with object properties
        $rule = ['filter' => [['var' => 'people'], ['>' => [['var' => 'age'], 28]]]];
        $data = [
            'people' => [
                ['name' => 'Alice', 'age' => 25],
                ['name' => 'Bob', 'age' => 30],
                ['name' => 'Charlie', 'age' => 35],
            ],
        ];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertCount(2, $result, 'Filter should return 2 people over 28');
        $this->assertEquals('Bob', $result[0]['name']);
        $this->assertEquals('Charlie', $result[1]['name']);
    }

    /**
     * Test reduce operation with proper context handling
     *
     * @return void
     */
    public function testReduceWithContextHandling(): void
    {
        // Test reduce with sum
        $rule = [
            'reduce' => [
                ['var' => 'integers'],
                ['+' => [['var' => 'current'], ['var' => 'accumulator']]],
                0,
            ],
        ];
        $data = ['integers' => [1, 2, 3, 4, 5]];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertEquals(15, $result, 'Reduce should sum all integers');

        // Test reduce with product
        $rule = [
            'reduce' => [
                ['var' => 'integers'],
                ['*' => [['var' => 'current'], ['var' => 'accumulator']]],
                1,
            ],
        ];
        $data = ['integers' => [2, 3, 4]];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertEquals(24, $result, 'Reduce should calculate product');

        // Test reduce with string concatenation
        $rule = [
            'reduce' => [
                ['var' => 'words'],
                ['cat' => [['var' => 'accumulator'], ['var' => 'current'], ' ']],
                '',
            ],
        ];
        $data = ['words' => ['Hello', 'World', 'Test']];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertEquals('Hello World Test ', $result, 'Reduce should concatenate strings');
    }

    /**
     * Test complex nested operations with proper context
     *
     * @return void
     */
    public function testComplexNestedOperationsWithContext(): void
    {
        // Test nested all/some operations
        $rule = [
            'all' => [
                ['var' => 'groups'],
                ['some' => [['var' => 'members'], ['>' => [['var' => 'score'], 80]]]],
            ],
        ];
        $data = [
            'groups' => [
                [
                    'name' => 'Team A',
                    'members' => [
                        ['name' => 'Alice', 'score' => 85],
                        ['name' => 'Bob', 'score' => 75],
                    ],
                ],
                [
                    'name' => 'Team B',
                    'members' => [
                        ['name' => 'Charlie', 'score' => 90],
                        ['name' => 'David', 'score' => 70],
                    ],
                ],
            ],
        ];
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertTrue($result, 'All groups should have at least one member with score > 80');

        // Test with one group failing
        $data['groups'][1]['members'][0]['score'] = 70; // Charlie now has 70
        $result = $this->evaluator->evaluate($rule, $data);
        $this->assertFalse($result, 'Should fail when one group has no members with score > 80');
    }
}
