<?php
declare(strict_types=1);

namespace RuleFlow\Rule\Logic;

use RuleFlow\Rule\AbstractJsonLogicRule;
use RuleFlow\Rule\JsonLogicRuleInterface;

/**
 * And Rule
 *
 * JsonLogic rule for logical AND operations
 */
class AndRule extends AbstractJsonLogicRule
{
    /**
     * The rules to AND together
     *
     * @var array<\RuleFlow\Rule\JsonLogicRuleInterface|array|mixed>
     */
    protected array $rules = [];

    /**
     * Constructor
     *
     * @param array<\RuleFlow\Rule\JsonLogicRuleInterface|array|mixed> $rules Rules to AND together
     */
    public function __construct(array $rules = [])
    {
        $this->operator = 'and';
        $this->rules = $rules;
    }

    /**
     * Add a rule to the AND condition
     *
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|array $rule Rule to add
     * @return $this
     */
    public function addRule(JsonLogicRuleInterface|array $rule)
    {
        $this->rules[] = $rule;

        return $this;
    }

    /**
     * Get all rules in the AND condition
     *
     * @return array<\RuleFlow\Rule\JsonLogicRuleInterface|array|mixed> Rules
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * @inheritDoc
     */
    protected function getOperands(): mixed
    {
        $operands = [];
        foreach ($this->rules as $rule) {
            $operands[] = $this->valueToArray($rule);
        }

        return $operands;
    }
}
