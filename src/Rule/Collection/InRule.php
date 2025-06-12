<?php
declare(strict_types=1);

namespace RuleFlow\Rule\Collection;

use RuleFlow\Rule\AbstractJsonLogicRule;
use RuleFlow\Rule\JsonLogicRuleInterface;

/**
 * In Rule
 *
 * JsonLogic rule for checking if a value is in a collection
 */
class InRule extends AbstractJsonLogicRule
{
    /**
     * The value to search for (needle)
     *
     * @var \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar
     */
    protected JsonLogicRuleInterface|array|string|int|float|bool $value;

    /**
     * The collection to search in (haystack)
     *
     * @var \RuleFlow\Rule\JsonLogicRuleInterface|array|string
     */
    protected JsonLogicRuleInterface|array|string $collection;

    /**
     * Constructor
     *
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar $value Value to search for
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|array|string $collection Collection to search in
     */
    public function __construct(
        JsonLogicRuleInterface|array|string|int|float|bool $value,
        JsonLogicRuleInterface|array|string $collection,
    ) {
        $this->operator = 'in';
        $this->value = $value;
        $this->collection = $collection;
    }

    /**
     * Get the value being searched for
     *
     * @return \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar Value
     */
    public function getValue(): JsonLogicRuleInterface|array|string|int|float|bool
    {
        return $this->value;
    }

    /**
     * Get the collection being searched in
     *
     * @return \RuleFlow\Rule\JsonLogicRuleInterface|array|string Collection
     */
    public function getCollection(): JsonLogicRuleInterface|array|string
    {
        return $this->collection;
    }

    /**
     * @inheritDoc
     */
    protected function getOperands(): mixed
    {
        return [
            $this->valueToArray($this->value),
            $this->valueToArray($this->collection),
        ];
    }
}
