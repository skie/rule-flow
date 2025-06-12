<?php
declare(strict_types=1);

namespace RuleFlow;

use RuleFlow\Rule\JsonLogicRuleInterface;

/**
 * Custom Rule Interface
 *
 * Interface for custom user-defined rules that can be dynamically registered
 * and evaluated by the RuleFlow system.
 */
interface CustomRuleInterface extends JsonLogicRuleInterface
{
    /**
     * Evaluate rule against resolved values and data context
     *
     * @param mixed $resolvedValues The resolved operand values from JSON Logic
     * @param mixed $data The original data context
     * @return mixed Rule evaluation result
     */
    public function evaluate(mixed $resolvedValues, mixed $data): mixed;
}
