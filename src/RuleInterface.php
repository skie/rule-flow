<?php
declare(strict_types=1);

namespace RuleFlow;

use Cake\Datasource\EntityInterface;

/**
 * Rule Interface
 *
 * Interface for rules
 */
interface RuleInterface
{
    /**
     * Evaluate rule against entity
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity to evaluate
     * @return bool Rule evaluation result
     */
    public function evaluate(EntityInterface $entity): bool;

    /**
     * Convert rule to standard array format
     *
     * @return array<string, mixed> Rule in standard array format
     */
    public function toArray(): array;
}
