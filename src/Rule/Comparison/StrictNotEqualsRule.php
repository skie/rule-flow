<?php
declare(strict_types=1);

namespace RuleFlow\Rule\Comparison;

use RuleFlow\Rule\AbstractJsonLogicRule;
use RuleFlow\Rule\JsonLogicRuleInterface;

/**
 * StrictNotEquals Rule
 *
 * JsonLogic rule for strict inequality comparison
 */
class StrictNotEqualsRule extends AbstractJsonLogicRule
{
    /**
     * Left operand
     *
     * @var \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar|null
     */
    protected JsonLogicRuleInterface|array|string|int|float|bool|null $left;

    /**
     * Right operand
     *
     * @var \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar|null
     */
    protected JsonLogicRuleInterface|array|string|int|float|bool|null $right;

    /**
     * Constructor
     *
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar|null $left Left operand
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar|null $right Right operand
     */
    public function __construct(JsonLogicRuleInterface|array|string|int|float|bool|null $left, JsonLogicRuleInterface|array|string|int|float|bool|null $right)
    {
        $this->operator = '!==';
        $this->left = $left;
        $this->right = $right;
    }

    /**
     * Get the left operand
     *
     * @return \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar|null Left operand
     */
    public function getLeft(): JsonLogicRuleInterface|array|string|int|float|bool|null
    {
        return $this->left;
    }

    /**
     * Get the right operand
     *
     * @return \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar|null Right operand
     */
    public function getRight(): JsonLogicRuleInterface|array|string|int|float|bool|null
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
