<?php
declare(strict_types=1);

namespace RuleFlow\Rule;

/**
 * Abstract JsonLogic Rule
 *
 * Base implementation for all JsonLogic rule classes
 */
abstract class AbstractJsonLogicRule implements JsonLogicRuleInterface
{
    /**
     * JsonLogic operator
     *
     * @var string
     */
    protected string $operator;

    /**
     * Get the operator for this rule
     *
     * @return string
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * Convert rule to JsonLogic array format
     *
     * @return array JsonLogic rule array
     */
    public function toArray(): array
    {
        return [$this->operator => $this->getOperands()];
    }

    /**
     * Get operands for this rule
     *
     * @return mixed Operands
     */
    abstract protected function getOperands(): mixed;

    /**
     * Convert rule objects to arrays recursively
     *
     * @param mixed $value Value to convert
     * @return mixed Converted value
     */
    protected function valueToArray(mixed $value): mixed
    {
        if ($value instanceof JsonLogicRuleInterface) {
            return $value->toArray();
        } elseif (is_array($value)) {
            $result = [];
            foreach ($value as $k => $v) {
                $result[$k] = $this->valueToArray($v);
            }

            return $result;
        }

        return $value;
    }
}
