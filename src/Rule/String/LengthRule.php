<?php
declare(strict_types=1);

namespace RuleFlow\Rule\String;

use RuleFlow\CustomRuleInterface;
use RuleFlow\Rule\AbstractJsonLogicRule;
use RuleFlow\Rule\JsonLogicRuleInterface;

/**
 * Length Rule
 *
 * JsonLogic rule for string and array length calculation.
 * Primarily used for form validation (minLength, maxLength, lengthBetween).
 *
 * Supported types:
 * - Strings: returns strlen()
 * - Arrays: returns count()
 * - Null: returns 0
 * - Other scalars: converts to string then returns strlen()
 */
class LengthRule extends AbstractJsonLogicRule implements CustomRuleInterface
{
    /**
     * The value to get length of (optional for rule building)
     *
     * @var \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar|null
     */
    protected JsonLogicRuleInterface|array|string|int|float|bool|null $value = null;

    /**
     * Constructor - can be called without arguments for registry
     *
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar|null $value Value to get length of
     */
    public function __construct(JsonLogicRuleInterface|array|string|int|float|bool|null $value = null)
    {
        $this->operator = 'length';
        $this->value = $value;
    }

    /**
     * Evaluate rule against resolved values and data context
     *
     * This method receives the resolved operands from JsonLogic evaluation
     *
     * @param mixed $resolvedValues The resolved operand values from JSON Logic
     * @param mixed $data The original data context
     * @return mixed Rule evaluation result
     */
    public function evaluate(mixed $resolvedValues, mixed $data): mixed
    {
        $value = $resolvedValues;

        if (is_array($value) && count($value) === 1 && isset($value[0])) {
            $value = $value[0];
        }

        if (is_string($value)) {
            return strlen($value);
        }

        if (is_array($value)) {
            return count($value);
        }

        if ($value === null) {
            return 0;
        }

        return strlen((string)$value);
    }

    /**
     * Get the value being measured (optional, for rule building)
     *
     * @return \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar|null Value
     */
    public function getValue(): JsonLogicRuleInterface|array|string|int|float|bool|null
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    protected function getOperands(): mixed
    {
        if ($this->value === null) {
            return null;
        }

        return $this->valueToArray($this->value);
    }

    /**
     * Create rule from array data
     *
     * @param mixed $data Value to get length of
     * @return self
     */
    public static function fromArray(mixed $data): self
    {
        return new self($data);
    }
}
