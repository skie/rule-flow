<?php
declare(strict_types=1);

namespace RuleFlow\Rule\String;

use RuleFlow\Rule\AbstractJsonLogicRule;
use RuleFlow\Rule\JsonLogicRuleInterface;

/**
 * Substr Rule
 *
 * JsonLogic rule for substring extraction
 */
class SubstrRule extends AbstractJsonLogicRule
{
    /**
     * The string to extract from
     *
     * @var \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar
     */
    protected JsonLogicRuleInterface|array|string|int|float|bool $string;

    /**
     * The start position
     *
     * @var \RuleFlow\Rule\JsonLogicRuleInterface|array|int
     */
    protected JsonLogicRuleInterface|array|int $start;

    /**
     * The length (optional)
     *
     * @var \RuleFlow\Rule\JsonLogicRuleInterface|array|int|null
     */
    protected JsonLogicRuleInterface|array|int|null $length = null;

    /**
     * Constructor
     *
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar $string String to extract from
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|array|int $start Start position
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|array|int|null $length Length (optional)
     */
    public function __construct(
        JsonLogicRuleInterface|array|string|int|float|bool $string,
        JsonLogicRuleInterface|array|int $start,
        JsonLogicRuleInterface|array|int|null $length = null,
    ) {
        $this->operator = 'substr';
        $this->string = $string;
        $this->start = $start;
        $this->length = $length;
    }

    /**
     * Get the string being extracted from
     *
     * @return \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar String
     */
    public function getString(): JsonLogicRuleInterface|array|string|int|float|bool
    {
        return $this->string;
    }

    /**
     * Get the start position
     *
     * @return \RuleFlow\Rule\JsonLogicRuleInterface|array|int Start
     */
    public function getStart(): JsonLogicRuleInterface|array|int
    {
        return $this->start;
    }

    /**
     * Get the length
     *
     * @return \RuleFlow\Rule\JsonLogicRuleInterface|array|int|null Length
     */
    public function getLength(): JsonLogicRuleInterface|array|int|null
    {
        return $this->length;
    }

    /**
     * @inheritDoc
     */
    protected function getOperands(): mixed
    {
        $operands = [
            $this->valueToArray($this->string),
            $this->valueToArray($this->start),
        ];

        if ($this->length !== null) {
            $operands[] = $this->valueToArray($this->length);
        }

        return $operands;
    }
}
