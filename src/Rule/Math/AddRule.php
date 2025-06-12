<?php
declare(strict_types=1);

namespace RuleFlow\Rule\Math;

use RuleFlow\Rule\AbstractJsonLogicRule;
use RuleFlow\Rule\JsonLogicRuleInterface;

/**
 * Add Rule
 *
 * JsonLogic rule for addition (can also be used for unary plus to cast to number)
 */
class AddRule extends AbstractJsonLogicRule
{
    /**
     * The values to add
     *
     * @var array<\RuleFlow\Rule\JsonLogicRuleInterface|array|scalar>
     */
    protected array $values = [];

    /**
     * Constructor
     *
     * @param array<\RuleFlow\Rule\JsonLogicRuleInterface|array|scalar> $values Values to add
     */
    public function __construct(array $values)
    {
        $this->operator = '+';
        $this->values = $values;
    }

    /**
     * Get the values being added
     *
     * @return array<\RuleFlow\Rule\JsonLogicRuleInterface|array|scalar> Values
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * Set the values to add
     *
     * @param array<\RuleFlow\Rule\JsonLogicRuleInterface|array|scalar> $values Values to add
     * @return $this
     */
    public function setValues(array $values)
    {
        $this->values = $values;

        return $this;
    }

    /**
     * Add a value to the addition
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
