<?php
declare(strict_types=1);

namespace RuleFlow\Rule\Logic;

use RuleFlow\Rule\AbstractJsonLogicRule;
use RuleFlow\Rule\JsonLogicRuleInterface;

/**
 * Or Rule
 *
 * JsonLogic rule for logical OR operations
 */
class OrRule extends AbstractJsonLogicRule
{
    /**
     * The rules to OR together
     *
     * @var array<\RuleFlow\Rule\JsonLogicRuleInterface|array|mixed>
     */
    protected array $rules = [];

    /**
     * Constructor
     *
     * @param array<\RuleFlow\Rule\JsonLogicRuleInterface|array|mixed> $rules Rules to OR together
     */
    public function __construct(array $rules = [])
    {
        $this->operator = 'or';
        $this->rules = $rules;
    }

    /**
     * Add a rule to the OR condition
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
     * Get all rules in the OR condition
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
