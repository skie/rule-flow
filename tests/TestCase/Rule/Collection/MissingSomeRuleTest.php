<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Rule\Collection;

use PHPUnit\Framework\TestCase;
use RuleFlow\Rule\Array\MergeRule;
use RuleFlow\Rule\Collection\MissingRule;
use RuleFlow\Rule\Collection\MissingSomeRule;
use RuleFlow\Rule\Conditional\IfRule;
use RuleFlow\Rule\JsonLogicRuleFactory;

/**
 * MissingSomeRule Test
 */
class MissingSomeRuleTest extends TestCase
{
    /**
     * Test constructor and getters
     *
     * @return void
     */
    public function testConstructorAndGetters(): void
    {
        $minRequired = 2;
        $keys = ['a', 'b', 'c'];

        $missingSomeRule = new MissingSomeRule($minRequired, $keys);

        $this->assertEquals('missing_some', $missingSomeRule->getOperator());
        $this->assertSame($minRequired, $missingSomeRule->getMinRequired());
        $this->assertSame($keys, $missingSomeRule->getKeys());
    }

    /**
     * Test setMinRequired method
     *
     * @return void
     */
    public function testSetMinRequired(): void
    {
        $missingSomeRule = new MissingSomeRule(1, ['a', 'b', 'c']);
        $this->assertSame(1, $missingSomeRule->getMinRequired());

        $missingSomeRule->setMinRequired(2);
        $this->assertSame(2, $missingSomeRule->getMinRequired());
    }

    /**
     * Test setKeys method
     *
     * @return void
     */
    public function testSetKeys(): void
    {
        $keys1 = ['a', 'b', 'c'];
        $missingSomeRule = new MissingSomeRule(1, $keys1);
        $this->assertSame($keys1, $missingSomeRule->getKeys());

        $keys2 = ['d', 'e', 'f'];
        $missingSomeRule->setKeys($keys2);
        $this->assertSame($keys2, $missingSomeRule->getKeys());
    }

    /**
     * Test toArray method with simple values
     *
     * @return void
     */
    public function testToArraySimple(): void
    {
        // Example from JsonLogic docs: {"missing_some":[1, ["a", "b", "c"]]}
        $missingSomeRule = new MissingSomeRule(1, ['a', 'b', 'c']);

        $expected = [
            'missing_some' => [
                1,
                ['a', 'b', 'c'],
            ],
        ];

        $this->assertEquals($expected, $missingSomeRule->toArray());
    }

    /**
     * Test with minimum one required (minimum satisfied example)
     *
     * @return void
     */
    public function testMinimumOneSatisfied(): void
    {
        // Example from JsonLogic docs where minimum is satisfied
        $missingSomeRule = new MissingSomeRule(1, ['a', 'b', 'c']);

        $expected = [
            'missing_some' => [
                1,
                ['a', 'b', 'c'],
            ],
        ];

        $this->assertEquals($expected, $missingSomeRule->toArray());
    }

    /**
     * Test with minimum two required (minimum not satisfied example)
     *
     * @return void
     */
    public function testMinimumTwoNotSatisfied(): void
    {
        // Example from JsonLogic docs where minimum is not satisfied
        $missingSomeRule = new MissingSomeRule(2, ['a', 'b', 'c']);

        $expected = [
            'missing_some' => [
                2,
                ['a', 'b', 'c'],
            ],
        ];

        $this->assertEquals($expected, $missingSomeRule->toArray());
    }

    /**
     * Test complex example from docs with form validation
     *
     * @return void
     */
    public function testComplexFormValidation(): void
    {
        // Example from JsonLogic docs: Form validation with required fields and at least one phone number
        // First part: Required fields
        $requiredFieldsRule = new MissingRule(['first_name', 'last_name']);

        // Second part: At least one phone number required
        $phoneFieldsRule = new MissingSomeRule(1, ['cell_phone', 'home_phone']);

        // Merge both rule results
        $mergeRule = new MergeRule([$requiredFieldsRule, $phoneFieldsRule]);

        // If any fields are missing, show error
        $ifRule = new IfRule([
            $mergeRule,
            'We require first name, last name, and one phone number.',
            'OK to proceed',
        ]);

        $expected = [
            'if' => [
                [
                    'merge' => [
                        [
                            'missing' => ['first_name', 'last_name'],
                        ],
                        [
                            'missing_some' => [
                                1,
                                ['cell_phone', 'home_phone'],
                            ],
                        ],
                    ],
                ],
                'We require first name, last name, and one phone number.',
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
        $missingSomeRule = new MissingSomeRule(1, ['a', 'b']);

        $returnedRule = $missingSomeRule
            ->setMinRequired(2)
            ->setKeys(['c', 'd', 'e']);

        $this->assertSame($missingSomeRule, $returnedRule);
        $this->assertEquals(2, $missingSomeRule->getMinRequired());
        $this->assertEquals(['c', 'd', 'e'], $missingSomeRule->getKeys());
    }

    /**
     * Test using factory methods
     *
     * @return void
     */
    public function testFactoryMethods(): void
    {
        $rule = JsonLogicRuleFactory::missingSome(
            2,
            ['email', 'phone', 'address'],
        );

        $expected = [
            'missing_some' => [
                2,
                ['email', 'phone', 'address'],
            ],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }
}
