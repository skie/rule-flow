<?php
declare(strict_types=1);

namespace RuleFlow\Rule\Conditional;

use InvalidArgumentException;
use RuleFlow\Rule\AbstractJsonLogicRule;

/**
 * If Rule
 *
 * JsonLogic rule for conditional operations (if-then-else)
 */
class IfRule extends AbstractJsonLogicRule
{
    /**
     * The conditions and results
     *
     * @var array<\RuleFlow\Rule\JsonLogicRuleInterface|array|mixed>
     */
    protected array $branches = [];

    /**
     * Constructor
     *
     * @param array<\RuleFlow\Rule\JsonLogicRuleInterface|array|mixed> $branches Conditions and results
     * @throws \InvalidArgumentException When invalid branches are provided
     */
    public function __construct(array $branches = [])
    {
        $this->operator = 'if';

        if (!empty($branches)) {
            $count = count($branches);
            if ($count < 2) {
                throw new InvalidArgumentException('If rule must have at least a condition and result');
            }

            $this->branches = $branches;
        }
    }

    /**
     * Add a condition-result pair
     *
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|mixed|array $condition The condition to check
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|mixed|array $success The result if condition is true
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|mixed|array $else The result if condition is false
     * @return $this
     */
    public function addBranch(mixed $condition, mixed $success, mixed $else = null)
    {
        $this->branches[] = $condition;
        $this->branches[] = $success;
        if ($else !== null) {
            $this->branches[] = $else;
        }

        return $this;
    }

    /**
     * Add else branch to the condition
     *
     * @param mixed $else Else value or rule
     * @return $this
     */
    public function addElse(mixed $else)
    {
        $this->branches[] = $else;

        return $this;
    }

    /**
     * Get the branches (conditions and results)
     *
     * @return array<\RuleFlow\Rule\JsonLogicRuleInterface|array|mixed> Branches
     */
    public function getBranches(): array
    {
        return $this->branches;
    }

    /**
     * @inheritDoc
     */
    protected function getOperands(): mixed
    {
        assert(count($this->branches) % 2 != 0, 'If rule must have an even number of branches');
        $operands = [];
        foreach ($this->branches as $branch) {
            $operands[] = $this->valueToArray($branch);
        }

        return $operands;
    }
}
