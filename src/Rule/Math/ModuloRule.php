<?php
declare(strict_types=1);

namespace RuleFlow\Rule\Math;

use RuleFlow\Rule\AbstractJsonLogicRule;
use RuleFlow\Rule\JsonLogicRuleInterface;

/**
 * Modulo Rule
 *
 * JsonLogic rule for modulo operation (remainder of division)
 */
class ModuloRule extends AbstractJsonLogicRule
{
    /**
     * The dividend (number to be divided)
     *
     * @var \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar
     */
    protected JsonLogicRuleInterface|array|string|int|float|bool $dividend;

    /**
     * The divisor (number to divide by)
     *
     * @var \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar
     */
    protected JsonLogicRuleInterface|array|string|int|float|bool $divisor;

    /**
     * Constructor
     *
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar $dividend Dividend
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar $divisor Divisor
     */
    public function __construct(
        JsonLogicRuleInterface|array|string|int|float|bool $dividend,
        JsonLogicRuleInterface|array|string|int|float|bool $divisor,
    ) {
        $this->operator = '%';
        $this->dividend = $dividend;
        $this->divisor = $divisor;
    }

    /**
     * Get the dividend
     *
     * @return \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar Dividend
     */
    public function getDividend(): JsonLogicRuleInterface|array|string|int|float|bool
    {
        return $this->dividend;
    }

    /**
     * Get the divisor
     *
     * @return \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar Divisor
     */
    public function getDivisor(): JsonLogicRuleInterface|array|string|int|float|bool
    {
        return $this->divisor;
    }

    /**
     * @inheritDoc
     */
    protected function getOperands(): mixed
    {
        return [
            $this->valueToArray($this->dividend),
            $this->valueToArray($this->divisor),
        ];
    }
}
