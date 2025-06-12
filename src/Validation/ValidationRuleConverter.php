<?php
declare(strict_types=1);

namespace RuleFlow\Validation;

use Cake\Validation\ValidationRule;
use Cake\Validation\ValidationSet;
use Cake\Validation\Validator;
use InvalidArgumentException;
use RuleFlow\Rule\JsonLogicRuleFactory as jl;
use RuleFlow\Rule\JsonLogicRuleInterface;

/**
 * ValidationRuleConverter class
 *
 * Converts CakePHP validation rules to JsonLogic format using the jl DSL.
 * Supports basic validation rules like equals, greaterThan, lessThan, empty checks, etc.
 */
class ValidationRuleConverter
{
    /**
     * Rule mapping from CakePHP validation rule names to converter methods
     *
     * @var array<string, string>
     */
    protected array $ruleMapping = [
        // Core CakePHP validation rules
        'notBlank' => 'convertNotBlank',
        'notEmpty' => 'convertNotEmpty',
        'comparison' => 'convertComparison',
        'compareFields' => 'convertCompareFields',
        'equals' => 'convertComparison',
        'notEquals' => 'convertComparison',
        'sameAs' => 'convertCompareFields',
        'notSameAs' => 'convertCompareFields',
        'greaterThan' => 'convertComparison',
        'greaterThanOrEqual' => 'convertComparison',
        'lessThan' => 'convertComparison',
        'lessThanOrEqual' => 'convertComparison',
        'lengthBetween' => 'convertLengthBetween',
        'range' => 'convertRange',
        'inList' => 'convertInList',
        'minLength' => 'convertMinLength',
        'maxLength' => 'convertMaxLength',
        'numeric' => 'convertNumeric',
        'integer' => 'convertInteger',
        'boolean' => 'convertBoolean',
        'email' => 'convertEmail',
        'url' => 'convertUrl',
        'ip' => 'convertIp',
        'uuid' => 'convertUuid',
        'between' => 'convertBetween',
        'hasAtLeast' => 'convertHasAtLeast',
        'hasAtMost' => 'convertHasAtMost',
        'multipleOptions' => 'convertMultipleOptions',
        'notInList' => 'convertNotInList',
    ];

    /**
     * Convert a CakePHP Validator to JsonLogic rules
     *
     * @param \Cake\Validation\Validator $validator CakePHP validator instance
     * @return array<string, array> Array of field names to rule structures with messages
     */
    public function convertValidator(Validator $validator): array
    {
        $jsonLogicRules = [];

        foreach ($validator as $fieldName => $validationSet) {
            $fieldRules = $this->convertValidationSet($fieldName, $validationSet);
            if (!empty($fieldRules)) {
                $jsonLogicRules[$fieldName] = [
                    'rules' => $fieldRules,
                ];
            }
        }

        return $jsonLogicRules;
    }

    /**
     * Convert a ValidationSet to JsonLogic rules
     *
     * @param string $fieldName Field name
     * @param \Cake\Validation\ValidationSet $validationSet Validation set
     * @return array<array>
     */
    public function convertValidationSet(string $fieldName, ValidationSet $validationSet): array
    {
        $jsonLogicRules = [];

        foreach ($validationSet as $ruleName => $validationRule) {
            $rule = $this->convertValidationRule($fieldName, $ruleName, $validationRule);
            if ($rule !== null) {
                $message = $validationRule->get('message') ?? "Validation failed for field '{$fieldName}' with rule '{$ruleName}'";
                $jsonLogicRules[] = [
                    'rule' => $rule->toArray(),
                    'message' => $message,
                ];
            }
        }

        return $jsonLogicRules;
    }

    /**
     * Convert a single ValidationRule to JsonLogic
     *
     * @param string $fieldName Field name
     * @param string $ruleName Rule name
     * @param \Cake\Validation\ValidationRule $validationRule Validation rule
     * @return \RuleFlow\Rule\JsonLogicRuleInterface|null
     */
    public function convertValidationRule(string $fieldName, string $ruleName, ValidationRule $validationRule): ?JsonLogicRuleInterface
    {
        $rule = $validationRule->get('rule');

        if (is_string($rule)) {
            // Method-based rule: $validator->greaterThan('age', 18)
            $actualRuleName = $rule;
            $ruleArgs = $validationRule->get('pass') ?? [];
        } elseif (is_array($rule)) {
            // Array-based rule: ['greaterThan', 18] or ['comparison', '>', 18]
            $actualRuleName = array_shift($rule);
            $ruleArgs = $rule; // Arguments are the remaining elements
        } else {
            // Skip callable rules for now
            return null;
        }

        // If we have a rule name from the array, use it, otherwise use the key name
        $ruleNameToUse = $actualRuleName ?? $ruleName;

        if (!isset($this->ruleMapping[$ruleNameToUse])) {
            // Skip unsupported rules
            return null;
        }

        $converterMethod = $this->ruleMapping[$ruleNameToUse];

        return $this->$converterMethod($fieldName, $ruleArgs);
    }

    /**
     * Convert comparison rule
     *
     * @param string $fieldName Field name
     * @param array $args Rule arguments
     * @return \RuleFlow\Rule\JsonLogicRuleInterface
     */
    protected function convertComparison(string $fieldName, array $args): JsonLogicRuleInterface
    {
        $operator = $args[0] ?? '';
        $value = $args[1] ?? null;

        $fieldVar = jl::var($fieldName);

        return $this->createComparisonRule($operator, $fieldVar, $value);
    }

    /**
     * Convert compareFields rule
     *
     * @param string $fieldName Field name
     * @param array $args Rule arguments
     * @return \RuleFlow\Rule\JsonLogicRuleInterface
     */
    protected function convertCompareFields(string $fieldName, array $args): JsonLogicRuleInterface
    {
        $otherField = $args[0] ?? '';
        $operator = $args[1] ?? '===';

        $field1Var = jl::var($fieldName);
        $field2Var = jl::var($otherField);

        return $this->createComparisonRule($operator, $field1Var, $field2Var);
    }

    /**
     * Create a comparison rule with the given operator and operands
     *
     * @param string $operator Comparison operator
     * @param mixed $left Left operand
     * @param mixed $right Right operand
     * @return \RuleFlow\Rule\JsonLogicRuleInterface
     */
    protected function createComparisonRule(string $operator, mixed $left, mixed $right): JsonLogicRuleInterface
    {
        return match ($operator) {
            '==' => jl::equals($left, $right),
            '!=' => jl::notEquals($left, $right),
            '===' => jl::strictEquals($left, $right),
            '!==' => jl::strictNotEquals($left, $right),
            '>' => jl::greaterThan($left, $right),
            '>=' => jl::greaterThanOrEqual($left, $right),
            '<' => jl::lessThan($left, $right),
            '<=' => jl::lessThanOrEqual($left, $right),
            default => throw new InvalidArgumentException("Unsupported comparison operator: $operator"),
        };
    }

    /**
     * Convert notBlank rule
     *
     * @param string $fieldName Field name
     * @param array $args Rule arguments
     * @return \RuleFlow\Rule\JsonLogicRuleInterface
     */
    protected function convertNotBlank(string $fieldName, array $args): JsonLogicRuleInterface
    {
        return jl::fieldNotEmpty($fieldName);
    }

    /**
     * Convert notEmpty rule
     *
     * @param string $fieldName Field name
     * @param array $args Rule arguments
     * @return \RuleFlow\Rule\JsonLogicRuleInterface
     */
    protected function convertNotEmpty(string $fieldName, array $args): JsonLogicRuleInterface
    {
        return jl::fieldNotEmpty($fieldName);
    }

    /**
     * Convert lengthBetween rule using JsonLogic betweenInclusive operation
     *
     * @param string $fieldName Field name
     * @param array $args Rule arguments
     * @return \RuleFlow\Rule\JsonLogicRuleInterface
     */
    protected function convertLengthBetween(string $fieldName, array $args): JsonLogicRuleInterface
    {
        $min = $args[0] ?? 0;
        $max = $args[1] ?? 255;

        $lengthVar = jl::length(jl::var($fieldName));

        return jl::betweenInclusive($min, $lengthVar, $max);
    }

    /**
     * Convert range rule using JsonLogic betweenInclusive operation
     *
     * @param string $fieldName Field name
     * @param array $args Rule arguments [min, max] (passed as separate parameters)
     * @return \RuleFlow\Rule\JsonLogicRuleInterface
     */
    protected function convertRange(string $fieldName, array $args): JsonLogicRuleInterface
    {
        $min = $args[0] ?? 0;
        $max = $args[1] ?? 100;
        $fieldVar = jl::var($fieldName);

        return jl::betweenInclusive($min, $fieldVar, $max);
    }

    /**
     * Convert inList rule
     *
     * @param string $fieldName Field name
     * @param array $args Rule arguments
     * @return \RuleFlow\Rule\JsonLogicRuleInterface
     */
    protected function convertInList(string $fieldName, array $args): JsonLogicRuleInterface
    {
        $list = $args[0] ?? [];

        return jl::in(jl::var($fieldName), $list);
    }

    /**
     * Convert minLength rule
     *
     * @param string $fieldName Field name
     * @param array $args Rule arguments
     * @return \RuleFlow\Rule\JsonLogicRuleInterface
     */
    protected function convertMinLength(string $fieldName, array $args): JsonLogicRuleInterface
    {
        $min = $args[0] ?? 0;

        return jl::greaterThanOrEqual(
            jl::length(jl::var($fieldName)),
            $min,
        );
    }

    /**
     * Convert maxLength rule
     *
     * @param string $fieldName Field name
     * @param array $args Rule arguments
     * @return \RuleFlow\Rule\JsonLogicRuleInterface
     */
    protected function convertMaxLength(string $fieldName, array $args): JsonLogicRuleInterface
    {
        $max = $args[0] ?? 255;

        return jl::lessThanOrEqual(
            jl::length(jl::var($fieldName)),
            $max,
        );
    }

    /**
     * Convert numeric rule
     *
     * @param string $fieldName Field name
     * @param array $args Rule arguments
     * @return \RuleFlow\Rule\JsonLogicRuleInterface
     */
    protected function convertNumeric(string $fieldName, array $args): JsonLogicRuleInterface
    {
        // JsonLogic doesn't have a direct numeric check, so we'll use a type comparison
        // This is a simplified implementation
        throw new InvalidArgumentException('Numeric validation rules are not yet supported in JsonLogic conversion');
    }

    /**
     * Convert integer rule
     *
     * @param string $fieldName Field name
     * @param array $args Rule arguments
     * @return \RuleFlow\Rule\JsonLogicRuleInterface
     */
    protected function convertInteger(string $fieldName, array $args): JsonLogicRuleInterface
    {
        // Check if the value is a number and has no decimal part
        throw new InvalidArgumentException('Integer validation rules are not yet supported in JsonLogic conversion');
    }

    /**
     * Convert boolean rule
     *
     * @param string $fieldName Field name
     * @param array $args Rule arguments
     * @return \RuleFlow\Rule\JsonLogicRuleInterface
     */
    protected function convertBoolean(string $fieldName, array $args): JsonLogicRuleInterface
    {
        throw new InvalidArgumentException('Boolean validation rules are not yet supported in JsonLogic conversion');
    }

    /**
     * Convert email rule
     *
     * @param string $fieldName Field name
     * @param array $args Rule arguments
     * @return \RuleFlow\Rule\JsonLogicRuleInterface
     */
    protected function convertEmail(string $fieldName, array $args): JsonLogicRuleInterface
    {
        // Email validation would require regex, which is not directly supported
        // For now, we'll throw an exception
        throw new InvalidArgumentException('Email validation rules are not yet supported in JsonLogic conversion');
    }

    /**
     * Convert url rule
     *
     * @param string $fieldName Field name
     * @param array $args Rule arguments
     * @return \RuleFlow\Rule\JsonLogicRuleInterface
     */
    protected function convertUrl(string $fieldName, array $args): JsonLogicRuleInterface
    {
        // URL validation would require regex, which is not directly supported
        // For now, we'll throw an exception
        throw new InvalidArgumentException('URL validation rules are not yet supported in JsonLogic conversion');
    }

    /**
     * Convert ip rule
     *
     * @param string $fieldName Field name
     * @param array $args Rule arguments
     * @return \RuleFlow\Rule\JsonLogicRuleInterface
     */
    protected function convertIp(string $fieldName, array $args): JsonLogicRuleInterface
    {
        // IP validation would require regex, which is not directly supported
        // For now, we'll throw an exception
        throw new InvalidArgumentException('IP validation rules are not yet supported in JsonLogic conversion');
    }

    /**
     * Convert uuid rule
     *
     * @param string $fieldName Field name
     * @param array $args Rule arguments
     * @return \RuleFlow\Rule\JsonLogicRuleInterface
     */
    protected function convertUuid(string $fieldName, array $args): JsonLogicRuleInterface
    {
        // UUID validation would require regex, which is not directly supported
        // For now, we'll throw an exception
        throw new InvalidArgumentException('UUID validation rules are not yet supported in JsonLogic conversion');
    }

    /**
     * Convert between rule using JsonLogic betweenInclusive operation
     *
     * @param string $fieldName Field name
     * @param array $args Rule arguments [min, max]
     * @return \RuleFlow\Rule\JsonLogicRuleInterface
     */
    protected function convertBetween(string $fieldName, array $args): JsonLogicRuleInterface
    {
        $min = $args[0] ?? 0;
        $max = $args[1] ?? 100;
        $fieldVar = jl::var($fieldName);

        // Use betweenInclusive for more efficient JsonLogic
        return jl::betweenInclusive($min, $fieldVar, $max);
    }

    /**
     * Convert hasAtLeast rule using JsonLogic operations
     *
     * @param string $fieldName Field name
     * @param array $args Rule arguments [count]
     * @return \RuleFlow\Rule\JsonLogicRuleInterface
     */
    protected function convertHasAtLeast(string $fieldName, array $args): JsonLogicRuleInterface
    {
        $minCount = $args[0] ?? 1;
        $fieldVar = jl::var($fieldName);

        // For simple array length check, use length directly
        if ($minCount === 1) {
            return jl::greaterThanOrEqual(
                jl::length($fieldVar),
                $minCount,
            );
        }

        // For counting non-empty elements, use reduce
        $countRule = jl::reduce(
            $fieldVar,
            jl::add([
                jl::var('accumulator'),
                jl::if([
                    jl::doubleBang(jl::var('current')),
                    1,
                    0,
                ]),
            ]),
            0,
        );

        return jl::greaterThanOrEqual($countRule, $minCount);
    }

    /**
     * Convert hasAtMost rule using JsonLogic operations
     *
     * @param string $fieldName Field name
     * @param array $args Rule arguments [count]
     * @return \RuleFlow\Rule\JsonLogicRuleInterface
     */
    protected function convertHasAtMost(string $fieldName, array $args): JsonLogicRuleInterface
    {
        $maxCount = $args[0] ?? 10;
        $fieldVar = jl::var($fieldName);

        // For simple array length check, use length directly
        if ($maxCount >= 0) {
            return jl::lessThanOrEqual(
                jl::length($fieldVar),
                $maxCount,
            );
        }

        // For counting non-empty elements, use reduce
        $countRule = jl::reduce(
            $fieldVar,
            jl::add([
                jl::var('accumulator'),
                jl::if([
                    jl::doubleBang(jl::var('current')),
                    1,
                    0,
                ]),
            ]),
            0,
        );

        return jl::lessThanOrEqual($countRule, $maxCount);
    }

    /**
     * Convert multipleOptions rule using JsonLogic filter operation
     *
     * @param string $fieldName Field name
     * @param array $args Rule arguments [options]
     * @return \RuleFlow\Rule\JsonLogicRuleInterface
     */
    protected function convertMultipleOptions(string $fieldName, array $args): JsonLogicRuleInterface
    {
        $options = $args[0] ?? [];
        $fieldVar = jl::var($fieldName);

        // Use filter to check if all selected values are in allowed options
        $validSelections = jl::filter(
            $fieldVar,
            jl::in(jl::var('current'), $options),
        );

        // Check if filtered array has same length as original (all values are valid)
        return jl::equals(
            jl::length($validSelections),
            jl::length($fieldVar),
        );
    }

    /**
     * Convert notInList rule using JsonLogic negation
     *
     * @param string $fieldName Field name
     * @param array $args Rule arguments [list]
     * @return \RuleFlow\Rule\JsonLogicRuleInterface
     */
    protected function convertNotInList(string $fieldName, array $args): JsonLogicRuleInterface
    {
        $list = $args[0] ?? [];

        return jl::not(
            jl::in(jl::var($fieldName), $list),
        );
    }

    /**
     * Add custom rule mapping
     *
     * @param string $ruleName CakePHP rule name
     * @param string $converterMethod Converter method name
     * @return void
     */
    public function addRuleMapping(string $ruleName, string $converterMethod): void
    {
        $this->ruleMapping[$ruleName] = $converterMethod;
    }

    /**
     * Get supported rule names
     *
     * @return array<string>
     */
    public function getSupportedRules(): array
    {
        return array_keys($this->ruleMapping);
    }

    /**
     * Check if a rule is supported
     *
     * @param string $ruleName Rule name
     * @return bool
     */
    public function isRuleSupported(string $ruleName): bool
    {
        return isset($this->ruleMapping[$ruleName]);
    }
}
