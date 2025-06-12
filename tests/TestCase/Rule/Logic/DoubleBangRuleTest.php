<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Rule\Logic;

use PHPUnit\Framework\TestCase;
use RuleFlow\Rule\Comparison\EqualsRule;
use RuleFlow\Rule\JsonLogicRuleFactory;
use RuleFlow\Rule\Logic\DoubleBangRule;
use RuleFlow\Rule\Variable\VarRule;

/**
 * DoubleBangRule Test
 */
class DoubleBangRuleTest extends TestCase
{
    /**
     * Test constructor and getters
     *
     * @return void
     */
    public function testConstructorAndGetters(): void
    {
        $value = new VarRule('active');

        $doubleBangRule = new DoubleBangRule($value);

        $this->assertEquals('!!', $doubleBangRule->getOperator());
        $this->assertSame($value, $doubleBangRule->getValue());
    }

    /**
     * Test setValue method
     *
     * @return void
     */
    public function testSetValue(): void
    {
        $value1 = new VarRule('active');
        $doubleBangRule = new DoubleBangRule($value1);
        $this->assertSame($value1, $doubleBangRule->getValue());

        $value2 = new VarRule('enabled');
        $doubleBangRule->setValue($value2);
        $this->assertSame($value2, $doubleBangRule->getValue());
    }

    /**
     * Test toArray method with rule
     *
     * @return void
     */
    public function testToArrayWithRule(): void
    {
        $value = new VarRule('active');
        $doubleBangRule = new DoubleBangRule($value);

        $expected = [
            '!!' => ['var' => 'active'],
        ];

        $this->assertEquals($expected, $doubleBangRule->toArray());
    }

    /**
     * Test toArray method with scalar value
     *
     * @return void
     */
    public function testToArrayWithScalar(): void
    {
        $doubleBangRule = new DoubleBangRule(0);

        $expected = [
            '!!' => 0,
        ];

        $this->assertEquals($expected, $doubleBangRule->toArray());
    }

    /**
     * Test toArray with complex rule
     *
     * @return void
     */
    public function testToArrayWithComplexRule(): void
    {
        $rule = new EqualsRule(new VarRule('status'), 'active');
        $doubleBangRule = new DoubleBangRule($rule);

        $expected = [
            '!!' => [
                '==' => [
                    ['var' => 'status'],
                    'active',
                ],
            ],
        ];

        $this->assertEquals($expected, $doubleBangRule->toArray());
    }

    /**
     * Test fluid interface
     *
     * @return void
     */
    public function testFluidInterface(): void
    {
        $doubleBangRule = new DoubleBangRule(null);

        $value = new VarRule('active');
        $returnedRule = $doubleBangRule->setValue($value);

        $this->assertSame($doubleBangRule, $returnedRule);
        $this->assertSame($value, $doubleBangRule->getValue());
    }

    /**
     * Test factory method
     *
     * @return void
     */
    public function testFactoryMethod(): void
    {
        $rule = JsonLogicRuleFactory::doubleBang(
            JsonLogicRuleFactory::var('status'),
        );

        $expected = [
            '!!' => ['var' => 'status'],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }
}
