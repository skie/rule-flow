<?php
declare(strict_types=1);

namespace RuleFlow\Rule\Comparison;

use RuleFlow\Rule\AbstractJsonLogicRule;
use RuleFlow\Rule\JsonLogicRuleInterface;

/**
 * LessThan Rule
 *
 * JsonLogic rule for less than comparison
 * Also supports between operation with 3 arguments (min < value < max)
 */
class LessThanRule extends AbstractJsonLogicRule
{
    /**
     * First operand
     *
     * @var \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar
     */
    protected JsonLogicRuleInterface|array|string|int|float|bool $first;

    /**
     * Second operand
     *
     * @var \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar
     */
    protected JsonLogicRuleInterface|array|string|int|float|bool $second;

    /**
     * Third operand (for between operation)
     *
     * @var \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar|null
     */
    protected JsonLogicRuleInterface|array|string|int|float|bool|null $third = null;

    /**
     * Constructor
     *
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar $first First operand
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar $second Second operand
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar|null $third Third operand (for between operation)
     */
    public function __construct(JsonLogicRuleInterface|array|string|int|float|bool $first, JsonLogicRuleInterface|array|string|int|float|bool $second, JsonLogicRuleInterface|array|string|int|float|bool|null $third = null)
    {
        $this->operator = '<';
        $this->first = $first;
        $this->second = $second;
        $this->third = $third;
    }

    /**
     * Get the first operand
     *
     * @return \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar First operand
     */
    public function getFirst(): JsonLogicRuleInterface|array|string|int|float|bool
    {
        return $this->first;
    }

    /**
     * Get the second operand
     *
     * @return \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar Second operand
     */
    public function getSecond(): JsonLogicRuleInterface|array|string|int|float|bool
    {
        return $this->second;
    }

    /**
     * Get the third operand (for between operation)
     *
     * @return \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar|null Third operand
     */
    public function getThird(): JsonLogicRuleInterface|array|string|int|float|bool|null
    {
        return $this->third;
    }

    /**
     * Check if this is a between operation
     *
     * @return bool
     */
    public function isBetween(): bool
    {
        return $this->third !== null;
    }

    /**
     * @inheritDoc
     */
    protected function getOperands(): mixed
    {
        if ($this->isBetween()) {
            return [
                $this->valueToArray($this->first),
                $this->valueToArray($this->second),
                $this->valueToArray($this->third),
            ];
        }

        return [
            $this->valueToArray($this->first),
            $this->valueToArray($this->second),
        ];
    }
}
