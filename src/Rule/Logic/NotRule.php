<?php
declare(strict_types=1);

namespace RuleFlow\Rule\Logic;

use RuleFlow\Rule\AbstractJsonLogicRule;

/**
 * Not Rule
 *
 * JsonLogic rule for logical NOT operations
 */
class NotRule extends AbstractJsonLogicRule
{
    /**
     * The rule to negate
     *
     * @var \RuleFlow\Rule\JsonLogicRuleInterface|mixed|array
     */
    protected mixed $rule;

    /**
     * Constructor
     *
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|mixed|array $rule Rule to negate
     */
    public function __construct(mixed $rule)
    {
        $this->operator = '!';
        $this->rule = $rule;
    }

    /**
     * Get the rule being negated
     *
     * @return \RuleFlow\Rule\JsonLogicRuleInterface|mixed|array The rule
     */
    public function getRule(): mixed
    {
        return $this->rule;
    }

    /**
     * Set the rule to negate
     *
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|mixed|array $rule Rule to negate
     * @return $this
     */
    public function setRule(mixed $rule)
    {
        $this->rule = $rule;

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function getOperands(): mixed
    {
        return $this->valueToArray($this->rule);
    }
}
