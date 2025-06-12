<?php
declare(strict_types=1);

namespace RuleFlow\Rule\Math;

use RuleFlow\Rule\AbstractJsonLogicRule;
use RuleFlow\Rule\JsonLogicRuleInterface;

/**
 * Max Rule
 *
 * JsonLogic rule for finding the maximum value
 */
class MaxRule extends AbstractJsonLogicRule
{
    /**
     * The values to compare
     *
     * @var array<\RuleFlow\Rule\JsonLogicRuleInterface|array|scalar>
     */
    protected array $values = [];

    /**
     * Constructor
     *
     * @param array<\RuleFlow\Rule\JsonLogicRuleInterface|array|scalar> $values Values to compare
     */
    public function __construct(array $values)
    {
        $this->operator = 'max';
        $this->values = $values;
    }

    /**
     * Get the values being compared
     *
     * @return array<\RuleFlow\Rule\JsonLogicRuleInterface|array|scalar> Values
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * Set the values to compare
     *
     * @param array<\RuleFlow\Rule\JsonLogicRuleInterface|array|scalar> $values Values to compare
     * @return $this
     */
    public function setValues(array $values)
    {
        $this->values = $values;

        return $this;
    }

    /**
     * Add a value to compare
     *
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar $value Value to add
     * @return $this
     */
    public function addValue(JsonLogicRuleInterface|array|string|int|float|bool $value)
    {
        $this->values[] = $value;

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function getOperands(): mixed
    {
        $operands = [];
        foreach ($this->values as $value) {
            $operands[] = $this->valueToArray($value);
        }

        return $operands;
    }
}
