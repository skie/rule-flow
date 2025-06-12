<?php
declare(strict_types=1);

namespace RuleFlow;

/**
 * JsonLogic Factory
 *
 * Factory for creating JsonLogic rules
 */
class JsonLogicFactory
{
    /**
     * Create an equals rule
     *
     * @param string $field Field name
     * @param mixed $value Value to compare
     * @return array Rule
     */
    public function equals(string $field, mixed $value): array
    {
        return ['==' => [['var' => $field], $value]];
    }

    /**
     * Create a not equals rule
     *
     * @param string $field Field name
     * @param mixed $value Value to compare
     * @return array Rule
     */
    public function notEquals(string $field, mixed $value): array
    {
        return ['!=' => [['var' => $field], $value]];
    }

    /**
     * Create a greater than rule
     *
     * @param string $field Field name
     * @param mixed $value Value to compare
     * @return array Rule
     */
    public function greaterThan(string $field, mixed $value): array
    {
        return ['>' => [['var' => $field], $value]];
    }

    /**
     * Create a less than rule
     *
     * @param string $field Field name
     * @param mixed $value Value to compare
     * @return array Rule
     */
    public function lessThan(string $field, mixed $value): array
    {
        return ['<' => [['var' => $field], $value]];
    }

    /**
     * Create a greater than or equal rule
     *
     * @param string $field Field name
     * @param mixed $value Value to compare
     * @return array Rule
     */
    public function greaterThanOrEqual(string $field, mixed $value): array
    {
        return ['>=' => [['var' => $field], $value]];
    }

    /**
     * Create a less than or equal rule
     *
     * @param string $field Field name
     * @param mixed $value Value to compare
     * @return array Rule
     */
    public function lessThanOrEqual(string $field, mixed $value): array
    {
        return ['<=' => [['var' => $field], $value]];
    }

    /**
     * Create a contains rule
     *
     * @param string $field Field name
     * @param mixed $value Value to check for
     * @return array Rule
     */
    public function contains(string $field, mixed $value): array
    {
        return ['contains' => [['var' => $field], $value]];
    }

    /**
     * Create a startsWith rule
     *
     * @param string $field Field name
     * @param string $value Value to check for
     * @return array Rule
     */
    public function startsWith(string $field, string $value): array
    {
        return ['startsWith' => [['var' => $field], $value]];
    }

    /**
     * Create an endsWith rule
     *
     * @param string $field Field name
     * @param string $value Value to check for
     * @return array Rule
     */
    public function endsWith(string $field, string $value): array
    {
        return ['endsWith' => [['var' => $field], $value]];
    }

    /**
     * Create an in rule
     *
     * @param string $field Field name
     * @param array $values Values to check for
     * @return array Rule
     */
    public function in(string $field, array $values): array
    {
        return ['in' => [['var' => $field], $values]];
    }

    /**
     * Create an AND rule
     *
     * @param array $rules Rules to AND together
     * @return array Rule
     */
    public function and(array $rules): array
    {
        return ['and' => $rules];
    }

    /**
     * Create an OR rule
     *
     * @param array $rules Rules to OR together
     * @return array Rule
     */
    public function or(array $rules): array
    {
        return ['or' => $rules];
    }

    /**
     * Create a NOT rule
     *
     * @param array $rule Rule to negate
     * @return array Rule
     */
    public function not(array $rule): array
    {
        return ['!' => $rule];
    }

    /**
     * Create an empty rule
     *
     * @param string $field Field name
     * @return array Rule
     */
    public function empty(string $field): array
    {
        return ['!' => [['var' => $field]]];
    }

    /**
     * Create a notEmpty rule
     *
     * @param string $field Field name
     * @return array Rule
     */
    public function notEmpty(string $field): array
    {
        return ['!!' => [['var' => $field]]];
    }

    /**
     * Create a between rule (exclusive)
     *
     * @param string $field Field name
     * @param mixed $min Minimum value
     * @param mixed $max Maximum value
     * @return array Rule
     */
    public function between(string $field, mixed $min, mixed $max): array
    {
        return ['>' => [$min, ['var' => $field], $max]];
    }

    /**
     * Create a between rule (inclusive)
     *
     * @param string $field Field name
     * @param mixed $min Minimum value
     * @param mixed $max Maximum value
     * @return array Rule
     */
    public function betweenInclusive(string $field, mixed $min, mixed $max): array
    {
        return ['>=' => [$min, ['var' => $field], $max]];
    }
}
