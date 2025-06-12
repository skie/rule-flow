<?php
declare(strict_types=1);

namespace RuleFlow\Rule\Array;

use RuleFlow\Rule\AbstractJsonLogicRule;
use RuleFlow\Rule\JsonLogicRuleInterface;

/**
 * Map Rule
 *
 * JsonLogic rule for mapping array elements
 */
class MapRule extends AbstractJsonLogicRule
{
    /**
     * The array to map
     *
     * @var \RuleFlow\Rule\JsonLogicRuleInterface|array
     */
    protected JsonLogicRuleInterface|array $array;

    /**
     * The transformation logic
     *
     * @var \RuleFlow\Rule\JsonLogicRuleInterface|array
     */
    protected JsonLogicRuleInterface|array $transform;

    /**
     * Constructor
     *
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|array $array Array to map
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|array $transform Transformation logic
     */
    public function __construct(JsonLogicRuleInterface|array $array, JsonLogicRuleInterface|array $transform)
    {
        $this->operator = 'map';
        $this->array = $array;
        $this->transform = $transform;
    }

    /**
     * Get the array being mapped
     *
     * @return \RuleFlow\Rule\JsonLogicRuleInterface|array Array
     */
    public function getArray(): JsonLogicRuleInterface|array
    {
        return $this->array;
    }

    /**
     * Get the transformation logic
     *
     * @return \RuleFlow\Rule\JsonLogicRuleInterface|array Transform
     */
    public function getTransform(): JsonLogicRuleInterface|array
    {
        return $this->transform;
    }

    /**
     * @inheritDoc
     */
    protected function getOperands(): mixed
    {
        return [
            $this->valueToArray($this->array),
            $this->valueToArray($this->transform),
        ];
    }
}
