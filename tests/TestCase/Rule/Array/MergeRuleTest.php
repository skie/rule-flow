<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Rule\Array;

use PHPUnit\Framework\TestCase;
use RuleFlow\Rule\Array\MergeRule;
use RuleFlow\Rule\Conditional\IfRule;
use RuleFlow\Rule\JsonLogicRuleFactory;
use RuleFlow\Rule\Variable\VarRule;

/**
 * MergeRule Test
 */
class MergeRuleTest extends TestCase
{
    /**
     * Test constructor and getters
     *
     * @return void
     */
    public function testConstructorAndGetters(): void
    {
        $arrays = [[1, 2], [3, 4]];

        $mergeRule = new MergeRule($arrays);

        $this->assertEquals('merge', $mergeRule->getOperator());
        $this->assertSame($arrays, $mergeRule->getArrays());
    }

    /**
     * Test setArrays method
     *
     * @return void
     */
    public function testSetArrays(): void
    {
        $arrays1 = [[1, 2], [3, 4]];
        $mergeRule = new MergeRule($arrays1);
        $this->assertSame($arrays1, $mergeRule->getArrays());

        $arrays2 = [[5, 6], [7, 8]];
        $mergeRule->setArrays($arrays2);
        $this->assertSame($arrays2, $mergeRule->getArrays());
    }

    /**
     * Test addArray method
     *
     * @return void
     */
    public function testAddArray(): void
    {
        $mergeRule = new MergeRule([[1, 2]]);
        $this->assertCount(1, $mergeRule->getArrays());

        $mergeRule->addArray([3, 4]);
        $this->assertCount(2, $mergeRule->getArrays());
        $this->assertSame([3, 4], $mergeRule->getArrays()[1]);

        $varRule = new VarRule('extras');
        $mergeRule->addArray($varRule);
        $this->assertCount(3, $mergeRule->getArrays());
        $this->assertSame($varRule, $mergeRule->getArrays()[2]);
    }

    /**
     * Test toArray method with simple arrays
     *
     * @return void
     */
    public function testToArraySimple(): void
    {
        // Example from JsonLogic docs: Merge two arrays
        $mergeRule = new MergeRule([[1, 2], [3, 4]]);

        $expected = [
            'merge' => [
                [1, 2],
                [3, 4],
            ],
        ];

        $this->assertEquals($expected, $mergeRule->toArray());
    }

    /**
     * Test toArray with mixed types (cast to arrays)
     *
     * @return void
     */
    public function testToArrayWithMixedTypes(): void
    {
        // Example from JsonLogic docs: Merge with non-array items
        $mergeRule = new MergeRule([1, 2, [3, 4]]);

        $expected = [
            'merge' => [
                1,
                2,
                [3, 4],
            ],
        ];

        $this->assertEquals($expected, $mergeRule->toArray());
    }

    /**
     * Test merge with complex example from docs (vehicle paperwork)
     *
     * @return void
     */
    public function testComplexMergeWithConditional(): void
    {
        // Vehicle paperwork example from docs:
        // Always require VIN, but only need APR and term if financing
        $baseFields = ['vin'];
        $financingFields = new IfRule([
            new VarRule('financing'),
            ['apr', 'term'],
            [],
        ]);

        $mergeRule = new MergeRule([$baseFields, $financingFields]);

        $expected = [
            'merge' => [
                ['vin'],
                [
                    'if' => [
                        ['var' => 'financing'],
                        ['apr', 'term'],
                        [],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $mergeRule->toArray());
    }

    /**
     * Test merge with variable arrays
     *
     * @return void
     */
    public function testMergeWithVariableArrays(): void
    {
        $array1 = new VarRule('requiredFields');
        $array2 = new VarRule('optionalFields');

        $mergeRule = new MergeRule([$array1, $array2]);

        $expected = [
            'merge' => [
                ['var' => 'requiredFields'],
                ['var' => 'optionalFields'],
            ],
        ];

        $this->assertEquals($expected, $mergeRule->toArray());
    }

    /**
     * Test fluid interface
     *
     * @return void
     */
    public function testFluidInterface(): void
    {
        $mergeRule = new MergeRule([]);

        $returnedRule = $mergeRule
            ->setArrays([[1, 2]])
            ->addArray([3, 4]);

        $this->assertSame($mergeRule, $returnedRule);
        $this->assertEquals([[1, 2], [3, 4]], $mergeRule->getArrays());
    }

    /**
     * Test using factory methods
     *
     * @return void
     */
    public function testFactoryMethods(): void
    {
        $rule = JsonLogicRuleFactory::merge([
            [1, 2],
            [3, 4],
            JsonLogicRuleFactory::var('extras'),
        ]);

        $expected = [
            'merge' => [
                [1, 2],
                [3, 4],
                ['var' => 'extras'],
            ],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }
}
