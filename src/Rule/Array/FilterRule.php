<?php
declare(strict_types=1);

namespace RuleFlow\Rule\Array;

use RuleFlow\Rule\AbstractJsonLogicRule;
use RuleFlow\Rule\JsonLogicRuleInterface;

/**
 * Filter Rule
 *
 * JsonLogic rule for filtering array elements
 */
class FilterRule extends AbstractJsonLogicRule
{
    /**
     * The array to filter
     *
     * @var \RuleFlow\Rule\JsonLogicRuleInterface|array
     */
    protected JsonLogicRuleInterface|array $array;

    /**
     * The filter logic
     *
     * @var \RuleFlow\Rule\JsonLogicRuleInterface|array
     */
    protected JsonLogicRuleInterface|array $filter;

    /**
     * Constructor
     *
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|array $array Array to filter
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|array $filter Filter logic
     */
    public function __construct(JsonLogicRuleInterface|array $array, JsonLogicRuleInterface|array $filter)
    {
        $this->operator = 'filter';
        $this->array = $array;
        $this->filter = $filter;
    }

    /**
     * Get the array being filtered
     *
     * @return \RuleFlow\Rule\JsonLogicRuleInterface|array Array
     */
    public function getArray(): JsonLogicRuleInterface|array
    {
        return $this->array;
    }

    /**
     * Get the filter logic
     *
     * @return \RuleFlow\Rule\JsonLogicRuleInterface|array Filter
     */
    public function getFilter(): JsonLogicRuleInterface|array
    {
        return $this->filter;
    }

    /**
     * @inheritDoc
     */
    protected function getOperands(): mixed
    {
        return [
            $this->valueToArray($this->array),
            $this->valueToArray($this->filter),
        ];
    }
}
