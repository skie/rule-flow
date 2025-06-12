<?php
declare(strict_types=1);

namespace RuleFlow;

use ArrayAccess;
use Cake\Core\InstanceConfigTrait;
use Cake\Datasource\EntityInterface;
use Cake\Utility\Hash;
use InvalidArgumentException;
use stdClass;
use Throwable;

/**
 * JsonLogic Evaluator
 *
 * Evaluates rules using JsonLogic syntax
 *
 * @see https:
 */
class JsonLogicEvaluator implements RuleEvaluatorInterface
{
    use InstanceConfigTrait;

    /**
     * Default configuration
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [];

    /**
     * @inheritDoc
     */
    public function evaluate($rule, mixed $entity): mixed
    {
        if ($rule === null || (is_array($rule) && empty($rule))) {
            return false;
        }

        if (!is_array($rule)) {
            return $rule;
        }

        if ($entity instanceof ArrayAccess) {
            if ($entity instanceof EntityInterface) {
                return $this->applyRule($rule, $entity->toArray());
            } elseif (is_iterable($entity)) {
                $data = [];
                foreach ($entity as $key => $value) {
                    $data[$key] = $value;
                }

                return $this->applyRule($rule, $data);
            }
        }

        return $this->applyRule($rule, $entity);
    }

    /**
     * Apply a rule to the data
     *
     * @param array $rule Rule to apply
     * @param mixed $data Data context
     * @return mixed Result of rule application
     */
    protected function applyRule(array $rule, mixed $data): mixed
    {
        if (empty($rule)) {
            return false;
        }

        $operator = key($rule);
        $values = $rule[$operator];

        if (is_int($operator)) {
            $result = [];
            foreach ($rule as $key => $value) {
                $result[$key] = $this->resolveValue($value, $data);
            }

            return $result;
        }

        $arrayData = is_array($data) ? $data : ['__scalar__' => $data];

        return $this->applyOperator((string)$operator, $values, $arrayData);
    }

    /**
     * Check if a value is truthy according to JsonLogic rules
     *
     * @param mixed $value Value to check
     * @return bool Result
     */
    protected function truthy(mixed $value): bool
    {
        if ($value === null || $value === false || $value === '' || $value === 0) {
            return false;
        }

        if ($value === '0') {
            return true;
        }

        if (is_array($value)) {
            return count($value) > 0;
        }

        return true;
    }

    /**
     * Apply an operator to values
     *
     * @param string $operator Operator to apply
     * @param mixed $values Values for the operator
     * @param array $data Data context
     * @return mixed Result of operator application
     */
    protected function applyOperator(string $operator, mixed $values, mixed $data): mixed
    {
        if (CustomRuleRegistry::hasCustomRule($operator)) {
            return $this->applyCustomRule($operator, $values, $data);
        }

        $arrayOperators = [
            'if', '?:', 'and', 'or', 'filter', 'map', 'reduce', 'all', 'none', 'some',
            '==', '===', '!=', '!==', '>', '>=', '<', '<=',
        ];

        if (in_array($operator, $arrayOperators) && !is_array($values)) {
            $values = [$values];
        }

        if ($operator === 'if' || $operator === '?:') {
            return $this->applyIfOperator($values, $data);
        }

        if ($operator === 'and') {
            return $this->applyAndOperator($values, $data);
        }

        if ($operator === 'or') {
            return $this->applyOrOperator($values, $data);
        }

        if ($operator === 'filter') {
            return $this->applyFilterOperator($values, $data);
        }

        if ($operator === 'map') {
            return $this->applyMapOperator($values, $data);
        }

        if ($operator === 'reduce') {
            return $this->applyReduceOperator($values, $data);
        }

        if ($operator === 'all') {
            return $this->applyAllOperator($values, $data);
        }

        if ($operator === 'none') {
            return $this->applyNoneOperator($values, $data);
        }

        if ($operator === 'some') {
            return $this->applySomeOperator($values, $data);
        }

        switch ($operator) {
            case 'value':
                return $values;

            case 'val':
                return $this->applyValOperator($values, $data);

            case 'var':
                $resolvedValues = $this->resolveValue($values, $data);

                return $this->getVariableValue($resolvedValues, $data);
            case 'missing':
                return $this->applyMissingOperator($values, $data);
            case 'missing_some':
                return $this->applyMissingSomeOperator($values, $data);

            case '!':
                return $this->applyNotOperator($values, $data);
            case '!!':
                return $this->applyDoubleNotOperator($values, $data);

            case '==':
                return $this->applyEqualsOperator($values, $data);
            case '===':
                return $this->applyStrictEqualsOperator($values, $data);
            case '!=':
                return $this->applyNotEqualsOperator($values, $data);
            case '!==':
                return $this->applyStrictNotEqualsOperator($values, $data);
            case '>':
                return $this->applyGreaterThanOperator($values, $data);
            case '>=':
                return $this->applyGreaterThanOrEqualOperator($values, $data);
            case '<':
                return $this->applyLessThanOperator($values, $data);
            case '<=':
                return $this->applyLessThanOrEqualOperator($values, $data);

            case '+':
                return $this->applyAddOperator($values, $data);
            case '-':
                return $this->applySubtractOperator($values, $data);
            case '*':
                return $this->applyMultiplyOperator($values, $data);
            case '/':
                return $this->applyDivideOperator($values, $data);
            case '%':
                return $this->applyModuloOperator($values, $data);
            case 'max':
                return $this->applyMaxOperator($values, $data);
            case 'min':
                return $this->applyMinOperator($values, $data);

            case 'merge':
                return $this->applyMergeOperator($values, $data);
            case 'in':
                return $this->applyInOperator($values, $data);

            case 'cat':
                return $this->applyCatOperator($values, $data);
            case 'substr':
                return $this->applySubstrOperator($values, $data);
            case 'contains':
                return $this->applyContainsOperator($values, $data);
            case 'startsWith':
                return $this->applyStartsWithOperator($values, $data);
            case 'endsWith':
                return $this->applyEndsWithOperator($values, $data);

            case 'log':
                return $this->applyLogOperator($values, $data);

            case 'preserve':
                return $this->applyPreserveOperator($values, $data);

            case 'keys':
                return $this->applyKeysOperator($values, $data);

            case 'exists':
                return $this->applyExistsOperator($values, $data);

            case '??':
                return $this->applyOrNullOperator($values, $data);

            case 'length':
                return $this->applyLengthOperator($values, $data);

            case 'eachKey':
                return $this->applyEachKeyOperator($values, $data);

            case 'get':
                return $this->applyGetOperator($values, $data);

            case 'try':
                return null;

            default:
                throw new InvalidArgumentException(sprintf('Unknown operator "%s"', $operator));
        }
    }

    /**
     * Apply missing operator
     *
     * Takes an array of data keys to search for, returns array of missing keys
     *
     * @param mixed $values Keys to check
     * @param mixed $data Data context
     * @return array Result
     */
    protected function applyMissingOperator(mixed $values, mixed $data): array
    {
        if ($this->isLogic($values) && !is_string($values)) {
            $values = $this->resolveValue($values, $data);
        }

        if (!is_array($values)) {
            $values = [$values];
        }

        $missing = [];
        foreach ($values as $key) {
            $unfound = new stdClass();
            $value = $this->getVariableValue($key, $data, $unfound);

            if ($value === $unfound || $value === null || $value === '') {
                $missing[] = $key;
            }
        }

        return $missing;
    }

    /**
     * Apply missing_some operator
     *
     * Takes min required keys and array of keys to search for
     *
     * @param array $values [min_required, keys_to_check]
     * @param mixed $data Data context
     * @return array Result
     */
    protected function applyMissingSomeOperator(array $values, mixed $data): array
    {
        if (count($values) < 2) {
            return [];
        }

        $min_required = $this->resolveValue($values[0], $data);
        $keys = $this->resolveValue($values[1], $data);

        if (!is_array($keys)) {
            $keys = [$keys];
        }

        $missing = $this->applyMissingOperator($keys, $data);

        $found = count($keys) - count($missing);

        if ($found >= $min_required) {
            return [];
        }

        return $missing;
    }

    /**
     * Apply if operator (ternary/conditional)
     *
     * @param array $values Values for the if statement [condition, trueResult, falseResult]
     * @param mixed $data Data context
     * @return mixed Result
     */
    protected function applyIfOperator(array $values, mixed $data): mixed
    {
        if (count($values) === 1) {
            return $this->resolveValue($values[0], $data);
        }

        if (count($values) < 2) {
            return null;
        }

        $values = array_values($values);

        if (count($values) % 2 === 0) {
            $values[] = null;
        }

        $onFalse = array_pop($values);

        $valuesCount = count($values);
        while ($valuesCount > 0) {
            $condition = array_shift($values);
            $onTrue = array_shift($values);

            $test = $this->resolveValue($condition, $data);

            if ($this->truthy($test)) {
                return $this->resolveValue($onTrue, $data);
            }
            $valuesCount = count($values);
        }

        return $this->resolveValue($onFalse, $data);
    }

    /**
     * Apply AND operator
     *
     * @param array $values Values to AND
     * @param mixed $data Data context
     * @return mixed Result
     */
    protected function applyAndOperator(array $values, mixed $data): mixed
    {
        if (!is_array($values)) {
            return $this->resolveValue($values, $data);
        }

        if (empty($values)) {
            return false;
        }

        $current = null;

        foreach ($values as $rule) {
            $current = $this->resolveValue($rule, $data);

            if (!$this->truthy($current)) {
                return $current;
            }
        }

        return $current;
    }

    /**
     * Apply OR operator
     *
     * @param array $values Values to OR
     * @param mixed $data Data context
     * @return mixed Result
     */
    protected function applyOrOperator(array $values, mixed $data): mixed
    {
        if (!is_array($values)) {
            return $this->resolveValue($values, $data);
        }

        if (empty($values)) {
            return false;
        }

        $current = null;

        foreach ($values as $rule) {
            $current = $this->resolveValue($rule, $data);

            if ($this->truthy($current)) {
                return $current;
            }
        }

        return $current;
    }

    /**
     * Apply NOT operator (logical negation)
     *
     * @param mixed $value Value to negate
     * @param array $data Data context
     * @return bool Result
     */
    protected function applyNotOperator(mixed $value, array $data): bool
    {
        if ($value === null) {
            return true;
        }

        if ($this->isLogic($value)) {
            $result = $this->resolveValue($value, $data);

            return !$this->truthy($result);
        }

        if (is_array($value) && count($value) === 1 && isset($value['var'])) {
            $varValue = $this->getVariableValue($value['var'], $data);

            return !$this->truthy($varValue);
        }

        if (!is_array($value)) {
            return !$this->truthy($this->resolveValue($value, $data));
        }

        if (count($value) === 0) {
            return true;
        }

        $firstItem = $value[0] ?? null;

        return !$this->truthy($this->resolveValue($firstItem, $data));
    }

    /**
     * Apply double NOT operator (cast to boolean)
     *
     * Returns truthy/falsy evaluation of the value
     *
     * @param mixed $value Value to cast
     * @param array $data Data context
     * @return bool Result
     */
    protected function applyDoubleNotOperator(mixed $value, array $data): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_array($value) && count($value) === 1 && isset($value['var'])) {
            $varValue = $this->getVariableValue($value['var'], $data);

            return $this->truthy($varValue);
        }

        if (!is_array($value)) {
            return $this->truthy($this->resolveValue($value, $data));
        }

        if (count($value) === 0) {
            return false;
        }

        $firstItem = $value[0] ?? null;

        if (is_array($firstItem) && count($firstItem) === 0) {
            return false;
        }

        return $this->truthy($this->resolveValue($firstItem, $data));
    }

    /**
     * Apply equals operator
     *
     * @param array $values Values to compare [a, b]
     * @param array $data Data context
     * @return bool Result
     */
    protected function applyEqualsOperator(mixed $values, mixed $data): bool
    {
        if (count($values) > 2) {
            $a = $this->resolveValue($values[0] ?? null, $data);
            foreach ($values as $value) {
                $value = $this->resolveValue($values[1] ?? null, $data);
                if ($value != $a) {
                    return false;
                }
            }

            return true;
        }

        $a = $this->resolveValue($values[0] ?? null, $data);
        $b = $this->resolveValue($values[1] ?? null, $data);

        $a = is_null($a) ? 0 : $a;
        $b = is_null($b) ? 0 : $b;

        return $a == $b;
    }

    /**
     * Apply strict equals operator
     *
     * @param array $values Values to compare [a, b]
     * @param array $data Data context
     * @return bool Result
     */
    protected function applyStrictEqualsOperator(mixed $values, array $data): bool
    {
        if (count($values) > 2) {
            $a = $this->resolveValue($values[0] ?? null, $data);
            foreach ($values as $value) {
                $value = $this->resolveValue($value ?? null, $data);
                if ($value !== $a) {
                    return false;
                }
            }

            return true;
        }

        $a = $this->resolveValue($values[0] ?? null, $data);
        $b = $this->resolveValue($values[1] ?? null, $data);

        return $a === $b;
    }

    /**
     * Apply not equals operator
     *
     * @param array $values Values to compare [a, b]
     * @param array $data Data context
     * @return bool Result
     */
    protected function applyNotEqualsOperator(mixed $values, array $data): bool
    {
        $a = $this->resolveValue($values[0] ?? null, $data);
        $b = $this->resolveValue($values[1] ?? null, $data);

        $a = is_null($a) ? 0 : $a;
        $b = is_null($b) ? 0 : $b;

        return $a != $b;
    }

    /**
     * Apply strict not equals operator
     *
     * @param array $values Values to compare [a, b]
     * @param array $data Data context
     * @return bool Result
     */
    protected function applyStrictNotEqualsOperator(mixed $values, array $data): bool
    {
        if (!array_key_exists(0, $values) || !array_key_exists(1, $values)) {
            return false;
        }

        $a = $this->resolveValue($values[0], $data);
        $b = $this->resolveValue($values[1], $data);

        return $a !== $b;
    }

    /**
     * Apply greater than operator
     *
     * @param array $values Values to compare [a, b]
     * @param array $data Data context
     * @return bool Result
     */
    protected function applyGreaterThanOperator(mixed $values, array $data): bool
    {
        if (empty($values)) {
            return false;
        }

        if (!is_array($values)) {
            return false;
        }

        if (count($values) === 1 && is_array($values[0])) {
            return $this->applyGreaterThanOperator($values[0], $data);
        }

        if (count($values) === 3) {
            if (!isset($values[0]) || !isset($values[1]) || !isset($values[2])) {
                return false;
            }

            $a = $this->resolveValue($values[0], $data);
            $b = $this->resolveValue($values[1], $data);
            $c = $this->resolveValue($values[2], $data);

            $a = is_null($a) ? 0 : $a;
            $b = is_null($b) ? 0 : $b;
            $c = is_null($c) ? 0 : $c;

            return ($a > $b) && ($b > $c);
        }

        if (count($values) < 2) {
            return false;
        }

        $a = $this->resolveValue($values[0], $data);
        $b = $this->resolveValue($values[1], $data);

        $a = is_null($a) ? 0 : $a;
        $b = is_null($b) ? 0 : $b;

        return $a > $b;
    }

    /**
     * Apply greater than or equal operator
     *
     * @param array $values Values to compare [a, b]
     * @param array $data Data context
     * @return bool Result
     */
    protected function applyGreaterThanOrEqualOperator(mixed $values, array $data): bool
    {
        if (empty($values)) {
            return false;
        }

        if (!is_array($values)) {
            return false;
        }

        if (count($values) === 1 && is_array($values[0])) {
            return $this->applyGreaterThanOrEqualOperator($values[0], $data);
        }

        if (count($values) === 3) {
            if (!isset($values[0]) || !isset($values[1]) || !isset($values[2])) {
                return false;
            }

            $a = $this->resolveValue($values[0], $data);
            $b = $this->resolveValue($values[1], $data);
            $c = $this->resolveValue($values[2], $data);

            $a = is_null($a) ? 0 : $a;
            $b = is_null($b) ? 0 : $b;
            $c = is_null($c) ? 0 : $c;

            return ($a >= $b) && ($b >= $c);
        }

        if (count($values) < 2) {
            return false;
        }

        $a = $this->resolveValue($values[0], $data);
        $b = $this->resolveValue($values[1], $data);

        $a = is_null($a) ? 0 : $a;
        $b = is_null($b) ? 0 : $b;

        return $a >= $b;
    }

    /**
     * Apply less than operator
     *
     * @param array $values Values to compare [a, b]
     * @param array $data Data context
     * @return bool Result
     */
    protected function applyLessThanOperator(mixed $values, array $data): bool
    {
        if (empty($values)) {
            return false;
        }

        if (!is_array($values)) {
            return false;
        }

        if (count($values) === 1 && is_array($values[0])) {
            return $this->applyLessThanOperator($values[0], $data);
        }

        if (count($values) === 3) {
            if (!isset($values[0]) || !isset($values[1]) || !isset($values[2])) {
                return false;
            }

            $a = $this->resolveValue($values[0], $data);
            $b = $this->resolveValue($values[1], $data);
            $c = $this->resolveValue($values[2], $data);

            $a = is_null($a) ? 0 : $a;
            $b = is_null($b) ? 0 : $b;
            $c = is_null($c) ? 0 : $c;

            return ($a < $b) && ($b < $c);
        }

        if (count($values) < 2) {
            return false;
        }

        $a = $this->resolveValue($values[0], $data);
        $b = $this->resolveValue($values[1], $data);

        $a = is_null($a) ? 0 : $a;
        $b = is_null($b) ? 0 : $b;

        return $a < $b;
    }

    /**
     * Apply less than or equal operator
     *
     * @param array $values Values to compare [a, b]
     * @param array $data Data context
     * @return bool Result
     */
    protected function applyLessThanOrEqualOperator(mixed $values, array $data): bool
    {
        if (empty($values)) {
            return false;
        }

        if (!is_array($values)) {
            return false;
        }

        if (count($values) === 1 && is_array($values[0])) {
            return $this->applyLessThanOrEqualOperator($values[0], $data);
        }

        if (count($values) === 3) {
            if (!isset($values[0]) || !isset($values[1]) || !isset($values[2])) {
                return false;
            }

            $a = $this->resolveValue($values[0], $data);
            $b = $this->resolveValue($values[1], $data);
            $c = $this->resolveValue($values[2], $data);

            $a = is_null($a) ? 0 : $a;
            $b = is_null($b) ? 0 : $b;
            $c = is_null($c) ? 0 : $c;

            return ($a <= $b) && ($b <= $c);
        }

        if (count($values) < 2) {
            return false;
        }

        $a = $this->resolveValue($values[0], $data);
        $b = $this->resolveValue($values[1], $data);

        $a = is_null($a) ? 0 : $a;
        $b = is_null($b) ? 0 : $b;

        return $a <= $b;
    }

    /**
     * Apply add operator
     *
     * @param mixed $values Values to add
     * @param array $data Data context
     * @return mixed Result
     */
    protected function applyAddOperator(mixed $values, array $data): mixed
    {
        if (!is_array($values)) {
            $result = $this->resolveValue($values, $data);

            if (!is_numeric($result)) {
                return 0;
            }

            return +$result;
        }

        if (empty($values)) {
            return 0;
        }

        $sum = 0;
        foreach ($values as $value) {
            $resolvedValue = $this->resolveValue($value, $data);

            if (is_array($resolvedValue)) {
                continue;
            } elseif (!is_numeric($resolvedValue) && !is_bool($resolvedValue)) {
                continue;
            } elseif (is_bool($resolvedValue)) {
                $resolvedValue = (int)$resolvedValue;
            }

            $sum += $resolvedValue;
        }

        return $sum;
    }

    /**
     * Apply subtract operator
     *
     * @param mixed $values Values to subtract
     * @param array $data Data context
     * @return mixed Result
     */
    protected function applySubtractOperator(mixed $values, array $data): mixed
    {
        if (!is_array($values)) {
            $result = $this->resolveValue($values, $data);

            if (!is_numeric($result)) {
                return 0;
            }

            return -$result;
        }

        if (empty($values)) {
            return 0;
        }

        if (count($values) === 1) {
            $result = $this->resolveValue($values[0], $data);

            if (!is_numeric($result)) {
                return 0;
            }

            return -$result;
        }

        $result = $this->resolveValue($values[0], $data);

        if (!is_numeric($result)) {
            $result = 0;
        }

        $valuesCount = count($values);
        for ($i = 1; $i < $valuesCount; $i++) {
            $subtrahend = $this->resolveValue($values[$i], $data);

            if (is_array($subtrahend)) {
                continue;
            } elseif (!is_numeric($subtrahend) && !is_bool($subtrahend)) {
                $subtrahend = 0;
            } elseif (is_bool($subtrahend)) {
                $subtrahend = (int)$subtrahend;
            }

            $result -= $subtrahend;
        }

        return $result;
    }

    /**
     * Apply multiply operator
     *
     * @param array $values Values to multiply
     * @param array $data Data context
     * @return mixed Result
     */
    protected function applyMultiplyOperator(array $values, array $data): mixed
    {
        if (!is_array($values)) {
            $result = $this->resolveValue($values, $data);

            if (!is_numeric($result)) {
                return 0;
            }

            return $result;
        }

        if (count($values) === 0) {
            return 1;
        }

        if (count($values) === 1) {
            $result = $this->resolveValue($values[0], $data);

            if (!is_numeric($result)) {
                return 0;
            }

            return $result;
        }

        $product = 1;
        foreach ($values as $value) {
            $factor = $this->resolveValue($value, $data);

            if (is_array($factor)) {
                return 0;
            } elseif (!is_numeric($factor) && !is_bool($factor)) {
                return 0;
            } elseif (is_bool($factor)) {
                $factor = (int)$factor;
            }

            $product *= $factor;
        }

        return $product;
    }

    /**
     * Apply divide operator
     *
     * @param mixed $values Values for division [dividend, divisor]
     * @param array $data Data context
     * @return mixed Result
     */
    protected function applyDivideOperator(mixed $values, array $data): mixed
    {
        if (!is_array($values)) {
            return $values;
        }

        if (count($values) === 0) {
            return null;
        }

        if (count($values) === 1) {
            $value = $this->resolveValue($values[0], $data);

            if (!is_numeric($value)) {
                return 0;
            }

            return $value;
        }

        if (!isset($values[0]) || !isset($values[1])) {
            return null;
        }

        $dividend = $this->resolveValue($values[0], $data);
        $divisor = $this->resolveValue($values[1], $data);

        if (is_array($dividend) || !is_numeric($dividend)) {
            return null;
        }

        if (is_array($divisor) || !is_numeric($divisor) || $divisor == 0) {
            return null;
        }

        try {
            $result = $dividend / $divisor;

            $valuesCount = count($values);
            for ($i = 2; $i < $valuesCount; $i++) {
                $additionalDivisor = $this->resolveValue($values[$i], $data);

                if (!is_numeric($additionalDivisor) || $additionalDivisor == 0) {
                    return null;
                }

                $result /= $additionalDivisor;
            }

            return $result;
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Apply modulo operator
     *
     * @param array $values Values for modulo [dividend, divisor]
     * @param array $data Data context
     * @return mixed Result
     */
    protected function applyModuloOperator(array $values, array $data): mixed
    {
        if (!is_array($values)) {
            return null;
        }

        if (count($values) < 2) {
            return null;
        }

        if (!isset($values[0]) || !isset($values[1])) {
            return null;
        }

        $dividend = $this->resolveValue($values[0], $data);
        $divisor = $this->resolveValue($values[1], $data);

        if (is_array($dividend) || !is_numeric($dividend)) {
            return null;
        }

        if (is_array($divisor) || !is_numeric($divisor) || $divisor == 0) {
            return null;
        }

        try {
            return $dividend % $divisor;
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Apply max operator
     *
     * @param array $values Values to find max from
     * @param array $data Data context
     * @return mixed Result
     */
    protected function applyMaxOperator(array $values, array $data): mixed
    {
        if (!is_array($values)) {
            $result = $this->resolveValue($values, $data);
            if (!is_numeric($result)) {
                return 0;
            }

            return $result;
        }

        if (empty($values)) {
            return null;
        }

        if (count($values) === 1) {
            $singleValue = $this->resolveValue($values[0], $data);

            if (is_array($singleValue)) {
                return $this->applyMaxOperator($singleValue, $data);
            }

            if (!is_numeric($singleValue)) {
                return 0;
            }

            return $singleValue;
        }

        $max = null;
        $foundNumeric = false;

        foreach ($values as $value) {
            $current = $this->resolveValue($value, $data);

            if (is_array($current) || !is_numeric($current)) {
                continue;
            }

            if (!$foundNumeric || $current > $max) {
                $max = $current;
                $foundNumeric = true;
            }
        }

        return $foundNumeric ? $max : null;
    }

    /**
     * Apply min operator
     *
     * @param array $values Values to find min from
     * @param array $data Data context
     * @return mixed Result
     */
    protected function applyMinOperator(array $values, array $data): mixed
    {
        if (!is_array($values)) {
            $result = $this->resolveValue($values, $data);
            if (!is_numeric($result)) {
                return 0;
            }

            return $result;
        }

        if (empty($values)) {
            return null;
        }

        if (count($values) === 1) {
            $singleValue = $this->resolveValue($values[0], $data);

            if (is_array($singleValue)) {
                return $this->applyMinOperator($singleValue, $data);
            }

            if (!is_numeric($singleValue)) {
                return 0;
            }

            return $singleValue;
        }

        $min = null;
        $foundNumeric = false;

        foreach ($values as $value) {
            $current = $this->resolveValue($value, $data);

            if (is_array($current) || !is_numeric($current)) {
                continue;
            }

            if (!$foundNumeric || $current < $min) {
                $min = $current;
                $foundNumeric = true;
            }
        }

        return $foundNumeric ? $min : null;
    }

    /**
     * Apply map operator
     *
     * @param array $values [array, logic]
     * @param array $data Data context
     * @return array Result
     */
    protected function applyMapOperator(array $values, array $data): array
    {
        if (!isset($values[0]) || !isset($values[1])) {
            throw new InvalidArgumentException('Invalid Arguments');
        }

        $array = $this->resolveValue($values[0], $data);
        $logic = $values[1];

        if ($array === null) {
            throw new InvalidArgumentException('Invalid Arguments');
        }

        if (!is_array($array)) {
            throw new InvalidArgumentException('Invalid Arguments');
        }

        $result = [];
        foreach ($array as $index => $item) {
            if (is_array($logic) && isset($logic['var']) && ($logic['var'] === '' || $logic['var'] === null)) {
                $result[] = $item;
                continue;
            }

            $itemData = [];

            if (is_array($item)) {
                $itemData = $item;
            } else {
                $itemData['_'] = $item;
            }

            $context = array_merge([], $data);

            $context['current'] = $item;
            $context['index'] = $index;

            $context = array_merge($context, $itemData);

            $result[] = $this->evaluateWithItemContext($logic, $context, $item);
        }

        return $result;
    }

    /**
     * Apply reduce operator
     *
     * @param array $values [array, logic, initial]
     * @param array $data Data context
     * @return mixed Result
     */
    protected function applyReduceOperator(array $values, array $data): mixed
    {
        $array = $this->resolveValue($values[0], $data);
        $logic = $values[1];
        $accumulator = isset($values[2]) ? $this->resolveValue($values[2], $data) : null;

        if (!is_array($array)) {
            return $accumulator;
        }

        foreach ($array as $current) {
            $itemData = [
                'current' => $current,
                'accumulator' => $accumulator,
            ];
            $accumulator = $this->resolveValue($logic, $itemData);
        }

        return $accumulator;
    }

    /**
     * Apply filter operator
     *
     * @param array $values [array, logic]
     * @param array $data Data context
     * @return array Result
     */
    protected function applyFilterOperator(array $values, array $data): array
    {
        if (!isset($values[0]) || !isset($values[1])) {
            throw new InvalidArgumentException('Invalid Arguments');
        }

        $array = $this->resolveValue($values[0], $data);
        $logic = $values[1];

        if ($array === null) {
            throw new InvalidArgumentException('Invalid Arguments');
        }

        if ($logic === null) {
            throw new InvalidArgumentException('Invalid Arguments');
        }

        if (!is_array($array)) {
            throw new InvalidArgumentException('Invalid Arguments');
        }

        $result = [];
        foreach ($array as $index => $item) {
            if (is_array($logic) && isset($logic['var']) && $logic['var'] === '') {
                if ($this->truthy($item)) {
                    $result[] = $item;
                }
                continue;
            }

            $itemData = [];

            if (is_array($item)) {
                $itemData = $item;
            } else {
                $itemData['_'] = $item;
            }

            $context = array_merge([], $data);

            $context['current'] = $item;
            $context['index'] = $index;

            $context = array_merge($context, $itemData);

            if ($this->truthy($this->evaluateWithItemContext($logic, $context, $item))) {
                $result[] = $item;
            }
        }

        return array_values($result);
    }

    /**
     * Apply all operator
     *
     * @param array $values [array, logic]
     * @param array $data Data context
     * @return bool Result
     */
    protected function applyAllOperator(array $values, array $data): bool
    {
        if (!isset($values[0])) {
            throw new InvalidArgumentException('Invalid Arguments');
        }

        $array = $this->resolveValue($values[0], $data);
        $logic = $values[1] ?? null;

        if ($array === null) {
            throw new InvalidArgumentException('Invalid Arguments');
        }

        if ($values[0] !== null && is_array($values[0]) && isset($values[0]['var'])) {
            $unfound = new stdClass();
            $testValue = $this->getVariableValue($values[0]['var'], $data, $unfound);
            if ($testValue === $unfound) {
                throw new InvalidArgumentException('Invalid Arguments');
            }
        }

        if (!is_array($array)) {
            throw new InvalidArgumentException('Invalid Arguments');
        }

        if ($logic === null) {
            throw new InvalidArgumentException('Invalid Arguments');
        }

        if (empty($array)) {
            return false;
        }

        foreach ($array as $item) {
            if (is_array($item)) {
                $context = array_merge($data, $item);
            } else {
                $context = $data;
                $context['_current_item'] = $item;
            }

            if (is_array($logic) && isset($logic['var']) && is_string($logic['var'])) {
                $prop = $logic['var'];

                if ($prop === '') {
                    if (!$this->truthy($item)) {
                        return false;
                    }
                    continue;
                }

                if (is_array($item) && isset($item[$prop])) {
                    $value = $item[$prop];
                } else {
                    $value = $this->getVariableValue($prop, $data, null);
                }

                if (!$this->truthy($value)) {
                    return false;
                }
                continue;
            }

            $itemContext = is_array($item) ? array_merge($data, $item) : $data;

            $result = $this->evaluateWithItemContext($logic, $itemContext, $item);

            if (!$this->truthy($result)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Evaluate logic with special handling for current item context
     *
     * In iteration contexts (all, some, etc.), {"var":""} should return the current item
     * being iterated over, not the entire data context.
     *
     * @param mixed $logic Logic to evaluate
     * @param array $data Data context
     * @param mixed $currentItem Current item being iterated over
     * @return mixed Result of evaluation
     */
    protected function evaluateWithItemContext(mixed $logic, array $data, mixed $currentItem): mixed
    {
        if (is_array($logic) && isset($logic['var']) && $logic['var'] === '') {
            return $currentItem;
        }

        if ($this->isLogic($logic)) {
            $operator = key($logic);
            $values = $logic[$operator];

            $processedValues = $this->processValuesForItemContext($values, $data, $currentItem);

            return $this->applyOperator($operator, $processedValues, $data);
        }

        return $this->resolveValue($logic, $data);
    }

    /**
     * Process values recursively, replacing {"var":""} with current item
     *
     * @param mixed $values Values to process
     * @param array $data Data context
     * @param mixed $currentItem Current item being iterated over
     * @return mixed Processed values
     */
    protected function processValuesForItemContext(mixed $values, array $data, mixed $currentItem): mixed
    {
        if (is_array($values)) {
            if (isset($values['var']) && $values['var'] === '') {
                return $currentItem;
            }

            $processed = [];
            foreach ($values as $key => $value) {
                $processed[$key] = $this->processValuesForItemContext($value, $data, $currentItem);
            }

            return $processed;
        }

        return $values;
    }

    /**
     * Apply none operator
     *
     * @param array $values [array, logic]
     * @param array $data Data context
     * @return bool Result
     */
    protected function applyNoneOperator(array $values, array $data): bool
    {
        if (!isset($values[0])) {
            throw new InvalidArgumentException('Invalid Arguments');
        }

        $array = $this->resolveValue($values[0], $data);
        $logic = $values[1] ?? null;

        if ($array === null) {
            throw new InvalidArgumentException('Invalid Arguments');
        }

        if (!is_array($array)) {
            throw new InvalidArgumentException('Invalid Arguments');
        }

        if ($logic === null) {
            throw new InvalidArgumentException('Invalid Arguments');
        }

        if (empty($array)) {
            return true;
        }

        foreach ($array as $item) {
            if (is_array($item)) {
                $context = array_merge($data, $item);
            } else {
                $context = $data;
                $context['_current_item'] = $item;
            }

            if (is_array($logic) && isset($logic['var']) && is_string($logic['var'])) {
                $prop = $logic['var'];

                if ($prop === '') {
                    if ($this->truthy($item)) {
                        return false;
                    }
                    continue;
                }

                if (is_array($item) && isset($item[$prop])) {
                    $value = $item[$prop];
                } else {
                    $value = $this->getVariableValue($prop, $data, null);
                }

                if ($this->truthy($value)) {
                    return false;
                }
                continue;
            }

            $itemContext = is_array($item) ? array_merge($data, $item) : $data;

            $result = $this->evaluateWithItemContext($logic, $itemContext, $item);

            if ($this->truthy($result)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Apply some operator
     *
     * @param array $values [array, logic]
     * @param array $data Data context
     * @return bool Result
     */
    protected function applySomeOperator(array $values, array $data): bool
    {
        if (!isset($values[0])) {
            throw new InvalidArgumentException('Invalid Arguments');
        }

        $array = $this->resolveValue($values[0], $data);
        $logic = $values[1] ?? null;

        if ($array === null) {
            throw new InvalidArgumentException('Invalid Arguments');
        }

        if (!is_array($array)) {
            throw new InvalidArgumentException('Invalid Arguments');
        }

        if ($logic === null) {
            throw new InvalidArgumentException('Invalid Arguments');
        }

        if (empty($array)) {
            return false;
        }

        foreach ($array as $item) {
            if (is_array($item)) {
                $context = array_merge($data, $item);
            } else {
                $context = $data;
                $context['_current_item'] = $item;
            }

            if (is_array($logic) && isset($logic['var']) && is_string($logic['var'])) {
                $prop = $logic['var'];

                if ($prop === '') {
                    if ($this->truthy($item)) {
                        return true;
                    }
                    continue;
                }

                if (is_array($item) && isset($item[$prop])) {
                    $value = $item[$prop];
                } else {
                    $value = $this->getVariableValue($prop, $data, null);
                }

                if ($this->truthy($value)) {
                    return true;
                }
                continue;
            }

            $itemContext = is_array($item) ? array_merge($data, $item) : $data;

            $result = $this->evaluateWithItemContext($logic, $itemContext, $item);

            if ($this->truthy($result)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Apply merge operator
     *
     * @param mixed $values Arrays to merge
     * @param array $data Data context
     * @return array Result
     */
    protected function applyMergeOperator(mixed $values, array $data): array
    {
        if (!is_array($values)) {
            $resolved = $this->resolveValue($values, $data);

            return is_array($resolved) ? $resolved : [$resolved];
        }

        $result = [];

        foreach ($values as $value) {
            $resolvedValue = $this->resolveValue($value, $data);

            if (!is_array($resolvedValue)) {
                $result[] = $resolvedValue;
            } else {
                $result = array_merge($result, $resolvedValue);
            }
        }

        return $result;
    }

    /**
     * Apply in operator
     *
     * @param array $values [needle, haystack]
     * @param array $data Data context
     * @return bool Result
     */
    protected function applyInOperator(array $values, array $data): bool
    {
        $needle = $this->resolveValue($values[0], $data);
        $haystack = $this->resolveValue($values[1], $data);

        if (is_string($haystack)) {
            return strpos($haystack, (string)$needle) !== false;
        }

        if (is_array($haystack)) {
            foreach ($haystack as $value) {
                if ($value == $needle) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Apply cat operator
     *
     * @param mixed $values Values to concatenate
     * @param array $data Data context
     * @return string Result
     */
    protected function applyCatOperator(mixed $values, array $data): string
    {
        if (!is_array($values)) {
            $resolved = $this->resolveValue($values, $data);

            return $this->convertToString($resolved);
        }

        $result = '';

        foreach ($values as $value) {
            $resolved = $this->resolveValue($value, $data);
            $result .= $this->convertToString($resolved);
        }

        return $result;
    }

    /**
     * Convert a value to string following JsonLogic rules
     *
     * @param mixed $value Value to convert
     * @return string String representation
     */
    protected function convertToString(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        return (string)$value;
    }

    /**
     * Apply substr operator
     *
     * @param array $values [string, start, length]
     * @param array $data Data context
     * @return string Result
     */
    protected function applySubstrOperator(array $values, array $data): string
    {
        $string = (string)$this->resolveValue($values[0], $data);
        $start = (int)$this->resolveValue($values[1], $data);

        if (count($values) > 2) {
            $length = (int)$this->resolveValue($values[2], $data);

            return substr($string, $start, $length);
        }

        return substr($string, $start);
    }

    /**
     * Apply contains operator
     *
     * @param array $values Values [haystack, needle]
     * @param array $data Data context
     * @return bool Result
     */
    protected function applyContainsOperator(array $values, array $data): bool
    {
        $haystack = $this->resolveValue($values[0], $data);
        $needle = $this->resolveValue($values[1], $data);

        if (is_string($haystack)) {
            return str_contains($haystack, $needle);
        }

        if (is_array($haystack)) {
            return in_array($needle, $haystack);
        }

        return false;
    }

    /**
     * Apply starts with operator
     *
     * @param array $values Values [haystack, needle]
     * @param array $data Data context
     * @return bool Result
     */
    protected function applyStartsWithOperator(array $values, array $data): bool
    {
        $haystack = $this->resolveValue($values[0], $data);
        $needle = $this->resolveValue($values[1], $data);

        if (!is_string($haystack) || !is_string($needle)) {
            return false;
        }

        return str_starts_with($haystack, $needle);
    }

    /**
     * Apply ends with operator
     *
     * @param array $values Values [haystack, needle]
     * @param array $data Data context
     * @return bool Result
     */
    protected function applyEndsWithOperator(array $values, array $data): bool
    {
        $haystack = $this->resolveValue($values[0], $data);
        $needle = $this->resolveValue($values[1], $data);

        if (!is_string($haystack) || !is_string($needle)) {
            return false;
        }

        return str_ends_with($haystack, $needle);
    }

    /**
     * Apply log operator
     *
     * @param mixed $value Value to log
     * @param array $data Data context
     * @return mixed Value (passes through unchanged)
     */
    protected function applyLogOperator(mixed $value, array $data): mixed
    {
        $resolved = $this->resolveValue($value, $data);

        if (is_array($resolved) || is_object($resolved)) {
            error_log(print_r($resolved, true));
        } else {
            error_log((string)$resolved);
        }

        return $resolved;
    }

    /**
     * Check if value is a logic rule
     *
     * @param mixed $value Value to check
     * @return bool Result
     */
    protected function isLogic(mixed $value): bool
    {
        return is_array($value)
            && !empty($value)
            && count($value) === 1
            && is_string(key($value));
    }

    /**
     * Get variable value from data
     *
     * @param mixed $path Path to variable
     * @param mixed $data Data context
     * @param mixed $default Value to return if variable not found
     * @return mixed Variable value
     */
    protected function getVariableValue(mixed $path, mixed $data, mixed $default = null): mixed
    {
        if ($path === '') {
            if (is_array($data) && isset($data['__scalar__']) && count($data) === 1) {
                return $data['__scalar__'];
            }

            return $data;
        }

        if ($path === null) {
            if (is_array($data) && isset($data['__scalar__']) && count($data) === 1) {
                return $data['__scalar__'];
            }

            return $data;
        }

        if (!is_array($data)) {
            return $default;
        }

        if (is_array($path)) {
            if (count($path) > 1) {
                $defaultValue = $this->resolveValue($path[1], $data);
                $pathValue = $path[0];

                try {
                    $value = $this->getVariableValue($pathValue, $data, $default);

                    return $value !== $default && $value !== null && $value !== ''
                        ? $value
                        : $defaultValue;
                } catch (Throwable $e) {
                    return $defaultValue;
                }
            }

            if (count($path) === 1 && !is_array($path[0])) {
                $path = $path[0];
            } else {
                if (count($path) === 0) {
                    if (isset($data['__scalar__']) && count($data) === 1) {
                        return $data['__scalar__'];
                    }

                    return $data;
                }

                return $default;
            }
        }

        if (!is_string($path) && !is_numeric($path)) {
            return $default;
        }

        if (isset($data[$path])) {
            return $data[$path];
        }

        if (is_string($path) && str_contains($path, '.')) {
            try {
                return Hash::get($data, $path, $default);
            } catch (Throwable $e) {
                return $default;
            }
        }

        return $default;
    }

    /**
     * Resolve a value, which might be a rule or a literal
     *
     * @param mixed $value Value to resolve
     * @param mixed $data Data context
     * @return mixed Resolved value
     */
    protected function resolveValue(mixed $value, mixed $data): mixed
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value) && empty($value)) {
            return $value;
        }

        if ($this->isLogic($value)) {
            try {
                $operator = key($value);
                $operand = $value[$operator];

                return $this->applyOperator($operator, $operand, $data);
            } catch (Throwable $e) {
                return null;
            }
        }

        if (
            is_array($value) && isset($value[0]) && is_string($value[0]) &&
            in_array($value[0], ['>=', '>', '<', '<=', '==', '===', '!=', '!==']) &&
            isset($value[1]) && is_array($value[1])
        ) {
            if (count($value[1]) < 2) {
                return false;
            }

            try {
                $operator = $value[0];
                $operands = $value[1];

                $left = $this->resolveValue($operands[0], $data);
                $right = $this->resolveValue($operands[1], $data);

                switch ($operator) {
                    case '>=':
                        return $left >= $right;
                    case '>':
                        return $left > $right;
                    case '<':
                        return $left < $right;
                    case '<=':
                        return $left <= $right;
                    case '==':
                        return $left == $right;
                    case '===':
                        return $left === $right;
                    case '!=':
                        return $left != $right;
                    case '!==':
                        return $left !== $right;
                    default:
                        return false;
                }
            } catch (Throwable $e) {
                return false;
            }
        }

        if (is_array($value)) {
            try {
                return array_map(function ($v) use ($data) {
                    return $this->resolveValue($v, $data);
                }, $value);
            } catch (Throwable $e) {
                return [];
            }
        }

        return $value;
    }

    /**
     * Get merge result for testing
     *
     * This is a public method specifically for testing the merge operator
     * which needs to return an array
     *
     * @param array $rule Rule to apply
     * @param array $data Data context
     * @return array Merged array result
     */
    public function getMergeResult(array $rule, array $data): array
    {
        if (!isset($rule['merge'])) {
            return [];
        }

        return $this->applyMergeOperator($rule['merge'], $data);
    }

    /**
     * Apply val operator
     * The val operator is used to fetch data from the context
     *
     * @param mixed $value Path to the data to fetch
     * @param array $data Data context
     * @return mixed Result
     */
    protected function applyValOperator(mixed $value, array $data): mixed
    {
        if ($value === null) {
            return null;
        }

        if (!is_array($value)) {
            if (!isset($data[$value])) {
                return null;
            }

            return $data[$value];
        }

        if (empty($value)) {
            return null;
        }

        if (is_array($value) && count($value) === 1) {
            if (!isset($value[0])) {
                return null;
            }

            return $data[$value[0]] ?? null;
        }

        $result = $data;

        foreach ($value as $key) {
            if (!is_string($key) && !is_int($key)) {
                return null;
            }

            if (is_array($result)) {
                if (isset($result[$key])) {
                    $result = $result[$key];
                } else {
                    return null;
                }
            } elseif (is_object($result)) {
                if (isset($result->$key)) {
                    $result = $result->$key;
                } else {
                    return null;
                }
            } else {
                return null;
            }
        }

        return $result;
    }

    /**
     * Apply preserve operator
     * This operator simply returns its input unchanged - useful for passing
     * data through a rule unchanged
     *
     * @param mixed $value Value to preserve
     * @param array $data Data context
     * @return mixed The input value, unchanged
     */
    protected function applyPreserveOperator(mixed $value, array $data): mixed
    {
        return $value;
    }

    /**
     * Apply keys operator
     * Returns the keys from an object
     *
     * @param mixed $value Value to get keys from
     * @param array $data Data context
     * @return array Keys from the object
     */
    protected function applyKeysOperator(mixed $value, array $data): array
    {
        $resolvedValue = $this->resolveValue($value, $data);

        if (!is_array($resolvedValue)) {
            return [];
        }

        return array_keys($resolvedValue);
    }

    /**
     * Apply exists operator
     * Checks if a variable exists in the data context
     *
     * @param mixed $key Variable path to check
     * @param array $data Data context
     * @return bool Whether the variable exists
     */
    protected function applyExistsOperator(mixed $key, array $data): bool
    {
        $unfound = new stdClass();
        $result = $this->getVariableValue($key, $data, $unfound);

        return $result !== $unfound;
    }

    /**
     * Apply OR-NULL operator (??)
     * Returns the first non-null and non-undefined value
     *
     * @param array $values Values to check
     * @param array $data Data context
     * @return mixed First non-null value or null
     */
    protected function applyOrNullOperator(array $values, array $data): mixed
    {
        if (empty($values)) {
            return null;
        }

        foreach ($values as $value) {
            $result = $this->resolveValue($value, $data);

            if ($result !== null && $result !== '') {
                return $result;
            }
        }

        return null;
    }

    /**
     * Apply length operator
     * Returns the length of a string or array
     *
     * @param mixed $value Value to get length of
     * @param array $data Data context
     * @return int Length of the value
     */
    protected function applyLengthOperator(mixed $value, array $data): int
    {
        $resolvedValue = $this->resolveValue($value, $data);

        if (is_array($resolvedValue) && count($resolvedValue) === 1 && isset($resolvedValue[0])) {
            $resolvedValue = $resolvedValue[0];
        }

        if (is_string($resolvedValue)) {
            return strlen($resolvedValue);
        } elseif (is_array($resolvedValue)) {
            return count($resolvedValue);
        } elseif (is_object($resolvedValue)) {
            if (method_exists($resolvedValue, 'count')) {
                return $resolvedValue->count();
            }

            return count(get_object_vars($resolvedValue));
        }

        throw new InvalidArgumentException('Cannot determine length of value');
    }

    /**
     * Apply eachKey operator
     * Applies a rule to each key in an object
     *
     * @param array $values [object, logic]
     * @param array $data Data context
     * @return array Result as array of [key, result] pairs
     */
    protected function applyEachKeyOperator(array $values, array $data): array
    {
        if (count($values) < 2) {
            return [];
        }

        $object = $this->resolveValue($values[0], $data);
        $logic = $values[1];

        if (!is_array($object)) {
            return [];
        }

        $result = [];
        foreach ($object as $key => $value) {
            $context = array_merge($data, [
                'key' => $key,
                'value' => $value,
                'current' => [
                    'key' => $key,
                    'value' => $value,
                ],
            ]);

            $result[] = [$key, $this->resolveValue($logic, $context)];
        }

        return $result;
    }

    /**
     * Apply get operator
     * Safely accesses a property of an object or index of an array
     *
     * @param array $values [object, property]
     * @param array $data Data context
     * @return mixed The property value or null if not found
     */
    protected function applyGetOperator(array $values, array $data): mixed
    {
        if (count($values) < 2) {
            return null;
        }

        $object = $this->resolveValue($values[0], $data);
        $property = $this->resolveValue($values[1], $data);

        $default = null;
        if (count($values) > 2) {
            $default = $this->resolveValue($values[2], $data);
        }

        if (is_array($object)) {
            return $object[$property] ?? $default;
        }

        if (is_object($object)) {
            return $object->$property ?? $default;
        }

        return $default;
    }

    /**
     * Apply custom rule
     *
     * @param string $operator Custom rule operator name
     * @param mixed $values Values for the custom rule
     * @param mixed $data Data context
     * @return mixed Result of custom rule evaluation
     */
    protected function applyCustomRule(string $operator, mixed $values, mixed $data): mixed
    {
        $customRule = CustomRuleRegistry::getRule($operator);
        if (!$customRule) {
            throw new InvalidArgumentException(sprintf('Custom rule "%s" not found in registry', $operator));
        }

        $resolvedValues = $this->resolveValue($values, $data);

        return $customRule->evaluate($resolvedValues, $data);
    }
}
