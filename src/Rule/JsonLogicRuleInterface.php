<?php
declare(strict_types=1);

namespace RuleFlow\Rule;

/**
 * JsonLogic Rule Interface
 *
 * Interface for all JsonLogic rule classes
 */
interface JsonLogicRuleInterface
{
    /**
     * Get the operator for this rule
     *
     * @return string Operator name
     */
    public function getOperator(): string;

    /**
     * Convert rule to JsonLogic array format
     *
     * @return array JsonLogic rule array
     */
    public function toArray(): array;
}
