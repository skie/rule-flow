<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Rule\Math;

use PHPUnit\Framework\TestCase;
use RuleFlow\Rule\Math\MultiplyRule;
use RuleFlow\Rule\Variable\VarRule;

/**
 * MultiplyRule Test
 */
class MultiplyRuleTest extends TestCase
{
    /**
     * Test constructor and getters
     *
     * @return void
     */
    public function testConstructorAndGetters(): void
    {
        $values = [5, 10, new VarRule('factor')];

        $multiplyRule = new MultiplyRule($values);

        $this->assertEquals('*', $multiplyRule->getOperator());
        $this->assertSame($values, $multiplyRule->getValues());
    }

    /**
     * Test setValues method
     *
     * @return void
     */
    public function testSetValues(): void
    {
        $values1 = [2, 3];
        $multiplyRule = new MultiplyRule($values1);
        $this->assertSame($values1, $multiplyRule->getValues());

        $values2 = [4, 5, 6];
        $multiplyRule->setValues($values2);
        $this->assertSame($values2, $multiplyRule->getValues());
    }

    /**
     * Test addValue method
     *
     * @return void
     */
    public function testAddValue(): void
    {
        $multiplyRule = new MultiplyRule([2, 3]);
        $this->assertCount(2, $multiplyRule->getValues());

        $multiplyRule->addValue(4);
        $this->assertCount(3, $multiplyRule->getValues());
        $this->assertSame(4, $multiplyRule->getValues()[2]);

        $varRule = new VarRule('factor');
        $multiplyRule->addValue($varRule);
        $this->assertCount(4, $multiplyRule->getValues());
        $this->assertSame($varRule, $multiplyRule->getValues()[3]);
    }

    /**
     * Test toArray method
     *
     * @return void
     */
    public function testToArray(): void
    {
        $values = [2, 3, new VarRule('factor')];
        $multiplyRule = new MultiplyRule($values);

        $expected = [
            '*' => [
                2,
                3,
                ['var' => 'factor'],
            ],
        ];

        $this->assertEquals($expected, $multiplyRule->toArray());
    }

    /**
     * Test with empty values array
     *
     * @return void
     */
    public function testEmptyValues(): void
    {
        $multiplyRule = new MultiplyRule([]);

        $expected = [
            '*' => [],
        ];

        $this->assertEquals($expected, $multiplyRule->toArray());
    }

    /**
     * Test with a single value
     *
     * @return void
     */
    public function testSingleValue(): void
    {
        $multiplyRule = new MultiplyRule([5]);

        $expected = [
            '*' => [5],
        ];

        $this->assertEquals($expected, $multiplyRule->toArray());
    }

    /**
     * Test fluid interface
     *
     * @return void
     */
    public function testFluidInterface(): void
    {
        $multiplyRule = new MultiplyRule([]);

        $returnedRule = $multiplyRule
            ->setValues([2, 3])
            ->addValue(4);

        $this->assertSame($multiplyRule, $returnedRule);
        $this->assertEquals([2, 3, 4], $multiplyRule->getValues());
    }
}
