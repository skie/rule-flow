<?php
declare(strict_types=1);

namespace RuleFlow\Rule\Array;

use RuleFlow\Rule\AbstractJsonLogicRule;
use RuleFlow\Rule\JsonLogicRuleInterface;

/**
 * Reduce Rule
 *
 * JsonLogic rule for reducing array to a single value
 */
class ReduceRule extends AbstractJsonLogicRule
{
    /**
     * The array to reduce
     *
     * @var \RuleFlow\Rule\JsonLogicRuleInterface|array
     */
    protected JsonLogicRuleInterface|array $array;

    /**
     * The reducer logic
     *
     * @var \RuleFlow\Rule\JsonLogicRuleInterface|array
     */
    protected JsonLogicRuleInterface|array $reducer;

    /**
     * The initial value
     *
     * @var \RuleFlow\Rule\JsonLogicRuleInterface|mixed|array
     */
    protected mixed $initial;

    /**
     * Constructor
     *
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|array $array Array to reduce
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|array $reducer Reducer logic
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|mixed|array $initial Initial value
     */
    public function __construct(
        JsonLogicRuleInterface|array $array,
        JsonLogicRuleInterface|array $reducer,
        mixed $initial,
    ) {
        $this->operator = 'reduce';
        $this->array = $array;
        $this->reducer = $reducer;
        $this->initial = $initial;
    }

    /**
     * Get the array being reduced
     *
     * @return \RuleFlow\Rule\JsonLogicRuleInterface|array Array
     */
    public function getArray(): JsonLogicRuleInterface|array
    {
        return $this->array;
    }

    /**
     * Get the reducer logic
     *
     * @return \RuleFlow\Rule\JsonLogicRuleInterface|array Reducer
     */
    public function getReducer(): JsonLogicRuleInterface|array
    {
        return $this->reducer;
    }

    /**
     * Get the initial value
     *
     * @return \RuleFlow\Rule\JsonLogicRuleInterface|mixed|array Initial
     */
    public function getInitial(): mixed
    {
        return $this->initial;
    }

    /**
     * @inheritDoc
     */
    protected function getOperands(): mixed
    {
        return [
            $this->valueToArray($this->array),
            $this->valueToArray($this->reducer),
            $this->valueToArray($this->initial),
        ];
    }
}
