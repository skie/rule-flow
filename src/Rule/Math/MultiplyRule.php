<?php
declare(strict_types=1);

namespace RuleFlow\Rule\Math;

use RuleFlow\Rule\AbstractJsonLogicRule;
use RuleFlow\Rule\JsonLogicRuleInterface;

/**
 * Multiply Rule
 *
 * JsonLogic rule for multiplication
 */
class MultiplyRule extends AbstractJsonLogicRule
{
    /**
     * The values to multiply
     *
     * @var array<\RuleFlow\Rule\JsonLogicRuleInterface|array|scalar>
     */
    protected array $values = [];

    /**
     * Constructor
     *
     * @param array<\RuleFlow\Rule\JsonLogicRuleInterface|array|scalar> $values Values to multiply
     */
    public function __construct(array $values)
    {
        $this->operator = '*';
        $this->values = $values;
    }

    /**
     * Get the values being multiplied
     *
     * @return array<\RuleFlow\Rule\JsonLogicRuleInterface|array|scalar> Values
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * Set the values to multiply
     *
     * @param array<\RuleFlow\Rule\JsonLogicRuleInterface|array|scalar> $values Values to multiply
     * @return $this
     */
    public function setValues(array $values)
    {
        $this->values = $values;

        return $this;
    }

    /**
     * Add a value to the multiplication
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
