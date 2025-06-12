<?php
declare(strict_types=1);

namespace RuleFlow\Rule\Array;

use RuleFlow\Rule\AbstractJsonLogicRule;
use RuleFlow\Rule\JsonLogicRuleInterface;

/**
 * Merge Rule
 *
 * JsonLogic rule for merging arrays
 */
class MergeRule extends AbstractJsonLogicRule
{
    /**
     * The arrays to merge
     *
     * @var array<\RuleFlow\Rule\JsonLogicRuleInterface|array>
     */
    protected array $arrays = [];

    /**
     * Constructor
     *
     * @param array<\RuleFlow\Rule\JsonLogicRuleInterface|array> $arrays Arrays to merge
     */
    public function __construct(array $arrays)
    {
        $this->operator = 'merge';
        $this->arrays = $arrays;
    }

    /**
     * Get the arrays being merged
     *
     * @return array<\RuleFlow\Rule\JsonLogicRuleInterface|array> Arrays
     */
    public function getArrays(): array
    {
        return $this->arrays;
    }

    /**
     * Set the arrays to merge
     *
     * @param array<\RuleFlow\Rule\JsonLogicRuleInterface|array> $arrays Arrays to merge
     * @return $this
     */
    public function setArrays(array $arrays)
    {
        $this->arrays = $arrays;

        return $this;
    }

    /**
     * Add an array to merge
     *
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|array $array Array to add
     * @return $this
     */
    public function addArray(JsonLogicRuleInterface|array $array)
    {
        $this->arrays[] = $array;

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function getOperands(): mixed
    {
        $operands = [];
        foreach ($this->arrays as $array) {
            $operands[] = $this->valueToArray($array);
        }

        return $operands;
    }
}
