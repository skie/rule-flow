<?php
declare(strict_types=1);

namespace RuleFlow\Rule\String;

use RuleFlow\Rule\AbstractJsonLogicRule;
use RuleFlow\Rule\JsonLogicRuleInterface;

/**
 * Cat Rule
 *
 * JsonLogic rule for string concatenation
 */
class CatRule extends AbstractJsonLogicRule
{
    /**
     * The strings to concatenate
     *
     * @var array<\RuleFlow\Rule\JsonLogicRuleInterface|array|scalar>
     */
    protected array $strings = [];

    /**
     * Constructor
     *
     * @param array<\RuleFlow\Rule\JsonLogicRuleInterface|array|scalar> $strings Strings to concatenate
     */
    public function __construct(array $strings)
    {
        $this->operator = 'cat';
        $this->strings = $strings;
    }

    /**
     * Get the strings being concatenated
     *
     * @return array<\RuleFlow\Rule\JsonLogicRuleInterface|array|scalar> Strings
     */
    public function getStrings(): array
    {
        return $this->strings;
    }

    /**
     * Set the strings to concatenate
     *
     * @param array<\RuleFlow\Rule\JsonLogicRuleInterface|array|scalar> $strings Strings to concatenate
     * @return $this
     */
    public function setStrings(array $strings)
    {
        $this->strings = $strings;

        return $this;
    }

    /**
     * Add a string to concatenate
     *
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar $string String to add
     * @return $this
     */
    public function addString(JsonLogicRuleInterface|array|string|int|float|bool $string)
    {
        $this->strings[] = $string;

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function getOperands(): mixed
    {
        $operands = [];
        foreach ($this->strings as $string) {
            $operands[] = $this->valueToArray($string);
        }

        return $operands;
    }
}
