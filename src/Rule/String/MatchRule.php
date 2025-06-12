<?php
declare(strict_types=1);

namespace RuleFlow\Rule\String;

use RuleFlow\CustomRuleInterface;
use RuleFlow\Rule\AbstractJsonLogicRule;
use RuleFlow\Rule\JsonLogicRuleInterface;
use Throwable;

/**
 * Match Rule
 *
 * JsonLogic rule for string matching using regex patterns
 */
class MatchRule extends AbstractJsonLogicRule implements CustomRuleInterface
{
    /**
     * The string to match against (optional for rule building)
     *
     * @var \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar|null
     */
    protected JsonLogicRuleInterface|array|string|int|float|bool|null $string = null;

    /**
     * The pattern to match (optional for rule building)
     *
     * @var \RuleFlow\Rule\JsonLogicRuleInterface|array|string|null
     */
    protected JsonLogicRuleInterface|array|string|null $pattern = null;

    /**
     * The flags to use (optional for rule building)
     *
     * @var \RuleFlow\Rule\JsonLogicRuleInterface|array|string|null
     */
    protected JsonLogicRuleInterface|array|string|null $flags = null;

    /**
     * Constructor - can be called without arguments for registry
     *
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar|null $string String to match against
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|array|string|null $pattern Pattern to match
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|array|string|null $flags Flags to use
     */
    public function __construct(
        JsonLogicRuleInterface|array|string|int|float|bool|null $string = null,
        JsonLogicRuleInterface|array|string|null $pattern = null,
        JsonLogicRuleInterface|array|string|null $flags = null,
    ) {
        $this->operator = 'match';
        $this->string = $string;
        $this->pattern = $pattern;
        $this->flags = $flags;
    }

    /**
     * Evaluate rule against resolved values and data context
     *
     * This method receives the resolved operands from JsonLogic evaluation
     *
     * @param mixed $resolvedValues The resolved operand values from JSON Logic
     * @param mixed $data The original data context
     * @return mixed Rule evaluation result
     */
    public function evaluate(mixed $resolvedValues, mixed $data): mixed
    {
        if (!is_array($resolvedValues) || count($resolvedValues) < 2) {
            return false;
        }

        $stringValue = (string)($resolvedValues[0] ?? '');
        $patternValue = (string)($resolvedValues[1] ?? '');
        $flagsValue = (string)($resolvedValues[2] ?? '');

        if (empty($patternValue)) {
            return false;
        }

        if (!preg_match('/^[\/\#\~\{\[].*[\}\]\/\#\~]$/', $patternValue)) {
            $patternValue = '/' . str_replace('/', '\/', $patternValue) . '/';
        }

        if (!empty($flagsValue)) {
            $patternValue = rtrim($patternValue, '/') . '/' . $flagsValue;
        }

        try {
            return preg_match($patternValue, $stringValue) === 1;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Get the string being matched against (optional, for rule building)
     *
     * @return \RuleFlow\Rule\JsonLogicRuleInterface|array|scalar|null String
     */
    public function getString(): JsonLogicRuleInterface|array|string|int|float|bool|null
    {
        return $this->string;
    }

    /**
     * Get the pattern (optional, for rule building)
     *
     * @return \RuleFlow\Rule\JsonLogicRuleInterface|array|string|null Pattern
     */
    public function getPattern(): JsonLogicRuleInterface|array|string|null
    {
        return $this->pattern;
    }

    /**
     * Get the flags (optional, for rule building)
     *
     * @return \RuleFlow\Rule\JsonLogicRuleInterface|array|string|null Flags
     */
    public function getFlags(): JsonLogicRuleInterface|array|string|null
    {
        return $this->flags;
    }

    /**
     * @inheritDoc
     */
    protected function getOperands(): mixed
    {
        if ($this->string === null || $this->pattern === null) {
            return null;
        }

        $operands = [
            $this->valueToArray($this->string),
            $this->valueToArray($this->pattern),
        ];

        if ($this->flags !== null) {
            $operands[] = $this->valueToArray($this->flags);
        }

        return $operands;
    }

    /**
     * Create rule from array data
     *
     * @param mixed $data Pattern to match against
     * @return self
     */
    public static function fromArray(mixed $data): self
    {
        if (is_array($data)) {
            if (isset($data['string']) || isset($data['pattern'])) {
                return new self(
                    $data['string'] ?? null,
                    $data['pattern'] ?? null,
                    $data['flags'] ?? null,
                );
            }

            if (!empty($data) && isset($data[0])) {
                return new self(
                    $data[0],
                    $data[1] ?? null,
                    $data[2] ?? null,
                );
            }
        }

        return new self($data);
    }
}
