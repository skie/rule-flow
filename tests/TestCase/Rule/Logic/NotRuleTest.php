<?php
declare(strict_types=1);

namespace RuleFlow\Test\TestCase\Rule\Logic;

use PHPUnit\Framework\TestCase;
use RuleFlow\Rule\Comparison\EqualsRule;
use RuleFlow\Rule\JsonLogicRuleFactory;
use RuleFlow\Rule\Logic\NotRule;
use RuleFlow\Rule\Variable\VarRule;

/**
 * Not Rule Test
 *
 * Tests for the NotRule class
 */
class NotRuleTest extends TestCase
{
    /**
     * Test toArray() with simple rule
     *
     * @return void
     */
    public function testToArraySimple(): void
    {
        $rule = new NotRule(
            new EqualsRule(new VarRule('status'), 'active'),
        );

        $expected = [
            '!' => ['==' => [['var' => 'status'], 'active']],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }

    /**
     * Test using the factory methods
     *
     * @return void
     */
    public function testFactoryMethods(): void
    {
        $rule = JsonLogicRuleFactory::not(
            JsonLogicRuleFactory::equals(
                JsonLogicRuleFactory::var('status'),
                'active',
            ),
        );

        $expected = [
            '!' => ['==' => [['var' => 'status'], 'active']],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }

    /**
     * Test setting rule
     *
     * @return void
     */
    public function testSetRule(): void
    {
        $rule = new NotRule(
            new EqualsRule(new VarRule('status'), 'active'),
        );

        $rule->setRule(new EqualsRule(new VarRule('type'), 'user'));

        $expected = [
            '!' => ['==' => [['var' => 'type'], 'user']],
        ];

        $this->assertEquals($expected, $rule->toArray());
    }
}
