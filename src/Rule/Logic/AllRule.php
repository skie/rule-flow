<?php
declare(strict_types=1);

namespace RuleFlow\Rule\Logic;

use RuleFlow\Rule\AbstractJsonLogicRule;
use RuleFlow\Rule\JsonLogicRuleInterface;

/**
 * All Rule
 *
 * JsonLogic rule for checking if all elements in a collection satisfy a condition
 */
class AllRule extends AbstractJsonLogicRule
{
    /**
     * The collection to check
     *
     * @var \RuleFlow\Rule\JsonLogicRuleInterface|array
     */
    protected JsonLogicRuleInterface|array $collection;

    /**
     * The condition to evaluate
     *
     * @var \RuleFlow\Rule\JsonLogicRuleInterface|array
     */
    protected JsonLogicRuleInterface|array $condition;

    /**
     * Constructor
     *
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|array $collection Collection to check
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|array $condition Condition to evaluate
     */
    public function __construct(JsonLogicRuleInterface|array $collection, JsonLogicRuleInterface|array $condition)
    {
        $this->operator = 'all';
        $this->collection = $collection;
        $this->condition = $condition;
    }

    /**
     * Get the collection being checked
     *
     * @return \RuleFlow\Rule\JsonLogicRuleInterface|array Collection
     */
    public function getCollection(): JsonLogicRuleInterface|array
    {
        return $this->collection;
    }

    /**
     * Set the collection to check
     *
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|array $collection Collection to check
     * @return $this
     */
    public function setCollection(JsonLogicRuleInterface|array $collection)
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     * Get the condition being evaluated
     *
     * @return \RuleFlow\Rule\JsonLogicRuleInterface|array Condition
     */
    public function getCondition(): JsonLogicRuleInterface|array
    {
        return $this->condition;
    }

    /**
     * Set the condition to evaluate
     *
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|array $condition Condition to evaluate
     * @return $this
     */
    public function setCondition(JsonLogicRuleInterface|array $condition)
    {
        $this->condition = $condition;

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function getOperands(): mixed
    {
        return [
            $this->valueToArray($this->collection),
            $this->valueToArray($this->condition),
        ];
    }
}
