<?php
declare(strict_types=1);

namespace RuleFlow\Rule\Logic;

use RuleFlow\Rule\AbstractJsonLogicRule;

/**
 * DoubleBang Rule
 *
 * JsonLogic rule for casting to boolean
 */
class DoubleBangRule extends AbstractJsonLogicRule
{
    /**
     * The value to cast to boolean
     *
     * @var \RuleFlow\Rule\JsonLogicRuleInterface|mixed|array
     */
    protected mixed $value;

    /**
     * Constructor
     *
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|mixed|array $value Value to cast
     */
    public function __construct(mixed $value)
    {
        $this->operator = '!!';
        $this->value = $value;
    }

    /**
     * Get the value being cast
     *
     * @return \RuleFlow\Rule\JsonLogicRuleInterface|mixed|array The value
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * Set the value to cast
     *
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|mixed|array $value Value to cast
     * @return $this
     */
    public function setValue(mixed $value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function getOperands(): mixed
    {
        return $this->valueToArray($this->value);
    }
}
