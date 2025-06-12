<?php
declare(strict_types=1);

namespace RuleFlow\Rule\Comparison;

use RuleFlow\Rule\AbstractJsonLogicRule;
use RuleFlow\Rule\JsonLogicRuleInterface;

/**
 * GreaterThan Rule
 *
 * JsonLogic rule for greater than comparison
 */
class GreaterThanRule extends AbstractJsonLogicRule
{
    /**
     * Left operand
     *
     * @var \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar
     */
    protected JsonLogicRuleInterface|array|string|int|float|bool $left;

    /**
     * Right operand
     *
     * @var \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar
     */
    protected JsonLogicRuleInterface|array|string|int|float|bool $right;

    /**
     * Constructor
     *
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar $left Left operand
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar $right Right operand
     */
    public function __construct(
        JsonLogicRuleInterface|array|string|int|float|bool $left,
        JsonLogicRuleInterface|array|string|int|float|bool $right,
    ) {
        $this->operator = '>';
        $this->left = $left;
        $this->right = $right;
    }

    /**
     * Get the left operand
     *
     * @return \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar Left operand
     */
    public function getLeft(): JsonLogicRuleInterface|array|string|int|float|bool
    {
        return $this->left;
    }

    /**
     * Get the right operand
     *
     * @return \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar Right operand
     */
    public function getRight(): JsonLogicRuleInterface|array|string|int|float|bool
    {
        return $this->right;
    }

    /**
     * @inheritDoc
     */
    protected function getOperands(): mixed
    {
        return [
            $this->valueToArray($this->left),
            $this->valueToArray($this->right),
        ];
    }
}
