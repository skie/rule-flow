<?php
declare(strict_types=1);

namespace RuleFlow;

/**
 * Rule Evaluator Interface
 *
 * Common interface for all rule evaluator implementations
 */
interface RuleEvaluatorInterface
{
    /**
     * Evaluate a rule against an entity
     *
     * @param mixed $rule Rule to evaluate
     * @param mixed $entity Entity to evaluate against
     * @return mixed Evaluation result
     */
    public function evaluate(mixed $rule, mixed $entity): mixed;
}
