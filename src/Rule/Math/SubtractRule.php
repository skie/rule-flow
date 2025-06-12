<?php
declare(strict_types=1);

namespace RuleFlow\Rule\Math;

use RuleFlow\Rule\AbstractJsonLogicRule;
use RuleFlow\Rule\JsonLogicRuleInterface;

/**
 * Subtract Rule
 *
 * JsonLogic rule for subtraction (can also be used for unary minus for negation)
 */
class SubtractRule extends AbstractJsonLogicRule
{
    /**
     * The values to subtract
     *
     * @var array<\RuleFlow\Rule\JsonLogicRuleInterface|array|scalar>
     */
    protected array $values = [];

    /**
     * Constructor
     *
     * @param array<\RuleFlow\Rule\JsonLogicRuleInterface|array|scalar> $values Values to subtract
     */
    public function __construct(array $values)
    {
        $this->operator = '-';
        $this->values = $values;
    }

    /**
     * Get the values being subtracted
     *
     * @return array<\RuleFlow\Rule\JsonLogicRuleInterface|array|scalar> Values
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * Set the values to subtract
     *
     * @param array<\RuleFlow\Rule\JsonLogicRuleInterface|array|scalar> $values Values to subtract
     * @return $this
     */
    public function setValues(array $values)
    {
        $this->values = $values;

        return $this;
    }

    /**
     * Add a value to the subtraction
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
