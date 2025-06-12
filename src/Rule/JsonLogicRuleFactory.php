<?php
declare(strict_types=1);

namespace RuleFlow\Rule;

use RuleFlow\Rule\Array\FilterRule;
use RuleFlow\Rule\Array\MapRule;
use RuleFlow\Rule\Array\MergeRule;
use RuleFlow\Rule\Array\ReduceRule;
use RuleFlow\Rule\Collection\InRule;
use RuleFlow\Rule\Collection\MissingRule;
use RuleFlow\Rule\Collection\MissingSomeRule;
use RuleFlow\Rule\Comparison\EqualsRule;
use RuleFlow\Rule\Comparison\GreaterThanOrEqualRule;
use RuleFlow\Rule\Comparison\GreaterThanRule;
use RuleFlow\Rule\Comparison\LessThanOrEqualRule;
use RuleFlow\Rule\Comparison\LessThanRule;
use RuleFlow\Rule\Comparison\NotEqualsRule;
use RuleFlow\Rule\Comparison\StrictEqualsRule;
use RuleFlow\Rule\Comparison\StrictNotEqualsRule;
use RuleFlow\Rule\Conditional\IfRule;
use RuleFlow\Rule\Logic\AllRule;
use RuleFlow\Rule\Logic\AndRule;
use RuleFlow\Rule\Logic\DoubleBangRule;
use RuleFlow\Rule\Logic\NoneRule;
use RuleFlow\Rule\Logic\NotRule;
use RuleFlow\Rule\Logic\OrRule;
use RuleFlow\Rule\Logic\SomeRule;
use RuleFlow\Rule\Math\AddRule;
use RuleFlow\Rule\Math\DivideRule;
use RuleFlow\Rule\Math\MaxRule;
use RuleFlow\Rule\Math\MinRule;
use RuleFlow\Rule\Math\ModuloRule;
use RuleFlow\Rule\Math\MultiplyRule;
use RuleFlow\Rule\Math\SubtractRule;
use RuleFlow\Rule\String\CatRule;
use RuleFlow\Rule\String\LengthRule;
use RuleFlow\Rule\String\MatchRule;
use RuleFlow\Rule\String\SubstrRule;
use RuleFlow\Rule\Variable\VarRule;

/**
 * JsonLogic Rule Factory
 *
 * Factory for creating JsonLogic rule objects
 */
class JsonLogicRuleFactory
{
    /**
     * Create a var rule
     *
     * @param array|string $path Variable path
     * @param mixed $defaultValue Default value if variable doesn't exist
     * @return \RuleFlow\Rule\Variable\VarRule
     */
    public static function var(string|array $path, mixed $defaultValue = null): VarRule
    {
        return new VarRule($path, $defaultValue);
    }

    /**
     * Create an equals rule
     *
     * @param mixed $left Left operand
     * @param mixed $right Right operand
     * @return \RuleFlow\Rule\Comparison\EqualsRule
     */
    public static function equals(mixed $left, mixed $right): EqualsRule
    {
        return new EqualsRule($left, $right);
    }

    /**
     * Create an equals rule for a field
     *
     * @param string $field Field to compare
     * @param mixed $rule Rule to compare with
     * @return \RuleFlow\Rule\Comparison\EqualsRule
     */
    public static function fieldEquals(string $field, mixed $rule): EqualsRule
    {
        return new EqualsRule(new VarRule($field), $rule);
    }

    /**
     * Create a not equals rule for a field
     *
     * @param string $field Field to compare
     * @param mixed $rule Rule to compare with
     * @return \RuleFlow\Rule\Comparison\NotEqualsRule
     */
    public static function fieldNotEquals(string $field, mixed $rule): NotEqualsRule
    {
        return new NotEqualsRule(new VarRule($field), $rule);
    }

    /**
     * Create an equals rule for two fields
     *
     * @param string $field1 Field to compare
     * @param string $field2 Field to compare with
     * @return \RuleFlow\Rule\Comparison\EqualsRule
     */
    public static function fieldsEquals(string $field1, string $field2): EqualsRule
    {
        return new EqualsRule(new VarRule($field1), new VarRule($field2));
    }

    /**
     * Create a not equals rule for two fields
     *
     * @param string $field1 Field to compare
     * @param string $field2 Field to compare with
     * @return \RuleFlow\Rule\Comparison\NotEqualsRule
     */
    public static function fieldsNotEquals(string $field1, string $field2): NotEqualsRule
    {
        return new NotEqualsRule(new VarRule($field1), new VarRule($field2));
    }

    /**
     * Create a not empty rule for a field
     *
     * @param string $field Field to check
     * @return \RuleFlow\Rule\Logic\DoubleBangRule
     */
    public static function fieldNotEmpty(string $field): DoubleBangRule
    {
        return new DoubleBangRule(new VarRule($field));
    }

    /**
     * Create a strict equals rule
     *
     * @param mixed $left Left operand
     * @param mixed $right Right operand
     * @return \RuleFlow\Rule\Comparison\StrictEqualsRule
     */
    public static function strictEquals(mixed $left, mixed $right): StrictEqualsRule
    {
        return new StrictEqualsRule($left, $right);
    }

    /**
     * Create a not equals rule
     *
     * @param mixed $left Left operand
     * @param mixed $right Right operand
     * @return \RuleFlow\Rule\Comparison\NotEqualsRule
     */
    public static function notEquals(mixed $left, mixed $right): NotEqualsRule
    {
        return new NotEqualsRule($left, $right);
    }

    /**
     * Create a not equals rule (alias for notEquals)
     *
     * @param mixed $left Left operand
     * @param mixed $right Right operand
     * @return \RuleFlow\Rule\Comparison\NotEqualsRule
     */
    public static function ne(mixed $left, mixed $right): NotEqualsRule
    {
        return self::notEquals($left, $right);
    }

    /**
     * Create a strict not equals rule
     *
     * @param mixed $left Left operand
     * @param mixed $right Right operand
     * @return \RuleFlow\Rule\Comparison\StrictNotEqualsRule
     */
    public static function strictNotEquals(mixed $left, mixed $right): StrictNotEqualsRule
    {
        return new StrictNotEqualsRule($left, $right);
    }

    /**
     * Create a greater than rule
     *
     * @param mixed $left Left operand
     * @param mixed $right Right operand
     * @return \RuleFlow\Rule\Comparison\GreaterThanRule
     */
    public static function greaterThan(mixed $left, mixed $right): GreaterThanRule
    {
        return new GreaterThanRule($left, $right);
    }

    /**
     * Create a greater than or equal rule
     *
     * @param mixed $left Left operand
     * @param mixed $right Right operand
     * @return \RuleFlow\Rule\Comparison\GreaterThanOrEqualRule
     */
    public static function greaterThanOrEqual(mixed $left, mixed $right): GreaterThanOrEqualRule
    {
        return new GreaterThanOrEqualRule($left, $right);
    }

    /**
     * Create a less than rule
     *
     * @param mixed $left Left operand
     * @param mixed $right Right operand
     * @return \RuleFlow\Rule\Comparison\LessThanRule
     */
    public static function lessThan(mixed $left, mixed $right): LessThanRule
    {
        return new LessThanRule($left, $right);
    }

    /**
     * Create a less than or equal rule
     *
     * @param mixed $left Left operand
     * @param mixed $right Right operand
     * @return \RuleFlow\Rule\Comparison\LessThanOrEqualRule
     */
    public static function lessThanOrEqual(mixed $left, mixed $right): LessThanOrEqualRule
    {
        return new LessThanOrEqualRule($left, $right);
    }

    /**
     * Create an AND rule
     *
     * @param array $rules Rules to AND together
     * @return \RuleFlow\Rule\Logic\AndRule
     */
    public static function and(array $rules = []): AndRule
    {
        return new AndRule($rules);
    }

    /**
     * Create an OR rule
     *
     * @param array $rules Rules to OR together
     * @return \RuleFlow\Rule\Logic\OrRule
     */
    public static function or(array $rules = []): OrRule
    {
        return new OrRule($rules);
    }

    /**
     * Create a NOT rule
     *
     * @param mixed $rule Rule to negate
     * @return \RuleFlow\Rule\Logic\NotRule
     */
    public static function not(mixed $rule): NotRule
    {
        return new NotRule($rule);
    }

    /**
     * Create a double bang (boolean cast) rule
     *
     * @param mixed $value Value to cast to boolean
     * @return \RuleFlow\Rule\Logic\DoubleBangRule
     */
    public static function doubleBang(mixed $value): DoubleBangRule
    {
        return new DoubleBangRule($value);
    }

    /**
     * Create an IN rule. This is a collection and string contains rule.
     *
     * @param mixed $value Value to search for
     * @param mixed $collection Collection to search in
     * @return \RuleFlow\Rule\Collection\InRule
     */
    public static function in(mixed $value, mixed $collection): InRule
    {
        return new InRule($value, $collection);
    }

    /**
     * Create a MISSING rule
     *
     * @param array<string|int> $keys Keys to check for
     * @return \RuleFlow\Rule\Collection\MissingRule
     */
    public static function missing(array $keys): MissingRule
    {
        return new MissingRule($keys);
    }

    /**
     * Create a MISSING_SOME rule
     *
     * @param int $minRequired Minimum number of keys required
     * @param array<string|int> $keys Keys to check
     * @return \RuleFlow\Rule\Collection\MissingSomeRule
     */
    public static function missingSome(int $minRequired, array $keys): MissingSomeRule
    {
        return new MissingSomeRule($minRequired, $keys);
    }

    /**
     * Create an IF rule
     *
     * @param array $branches Conditions and results
     * @return \RuleFlow\Rule\Conditional\IfRule
     */
    public static function if(array $branches = []): IfRule
    {
        return new IfRule($branches);
    }

    /**
     * Create a MAX rule
     *
     * @param array $values Values to compare
     * @return \RuleFlow\Rule\Math\MaxRule
     */
    public static function max(array $values): MaxRule
    {
        return new MaxRule($values);
    }

    /**
     * Create a MIN rule
     *
     * @param array $values Values to compare
     * @return \RuleFlow\Rule\Math\MinRule
     */
    public static function min(array $values): MinRule
    {
        return new MinRule($values);
    }

    /**
     * Create a CAT rule for string concatenation
     *
     * @param array $strings Strings to concatenate
     * @return \RuleFlow\Rule\String\CatRule
     */
    public static function cat(array $strings): CatRule
    {
        return new CatRule($strings);
    }

    /**
     * Create a LENGTH rule for string length
     *
     * @param mixed $string String to get length of
     * @return \RuleFlow\Rule\String\LengthRule
     */
    public static function length(mixed $string): LengthRule
    {
        return new LengthRule($string);
    }

    /**
     * Create a MATCH rule for string matching using regex
     *
     * @param mixed $string String to match
     * @param mixed $pattern Pattern to match
     * @param mixed $flags Flags to use
     * @return \RuleFlow\Rule\String\MatchRule
     */
    public static function match(mixed $string, mixed $pattern, mixed $flags = null): MatchRule
    {
        return new MatchRule($string, $pattern, $flags);
    }

    /**
     * Create a SUBSTR rule for substring extraction
     *
     * @param mixed $string String to extract from
     * @param mixed $start Start position
     * @param mixed|null $length Length (optional)
     * @return \RuleFlow\Rule\String\SubstrRule
     */
    public static function substr(mixed $string, mixed $start, mixed $length = null): SubstrRule
    {
        return new SubstrRule($string, $start, $length);
    }

    /**
     * Create a MAP rule for array mapping
     *
     * @param mixed $array Array to map
     * @param mixed $transform Transformation logic
     * @return \RuleFlow\Rule\Array\MapRule
     */
    public static function map(mixed $array, mixed $transform): MapRule
    {
        return new MapRule($array, $transform);
    }

    /**
     * Create a FILTER rule for array filtering
     *
     * @param mixed $array Array to filter
     * @param mixed $filter Filter logic
     * @return \RuleFlow\Rule\Array\FilterRule
     */
    public static function filter(mixed $array, mixed $filter): FilterRule
    {
        return new FilterRule($array, $filter);
    }

    /**
     * Create a REDUCE rule for array reduction
     *
     * @param mixed $array Array to reduce
     * @param mixed $reducer Reducer logic
     * @param mixed $initial Initial value
     * @return \RuleFlow\Rule\Array\ReduceRule
     */
    public static function reduce(mixed $array, mixed $reducer, mixed $initial): ReduceRule
    {
        return new ReduceRule($array, $reducer, $initial);
    }

    /**
     * Create a MERGE rule for merging arrays
     *
     * @param array $arrays Arrays to merge
     * @return \RuleFlow\Rule\Array\MergeRule
     */
    public static function merge(array $arrays): MergeRule
    {
        return new MergeRule($arrays);
    }

    /**
     * Create an ADD rule for addition
     *
     * @param array $values Values to add
     * @return \RuleFlow\Rule\Math\AddRule
     */
    public static function add(array $values): AddRule
    {
        return new AddRule($values);
    }

    /**
     * Create a SUBTRACT rule for subtraction
     *
     * @param array $values Values to subtract
     * @return \RuleFlow\Rule\Math\SubtractRule
     */
    public static function subtract(array $values): SubtractRule
    {
        return new SubtractRule($values);
    }

    /**
     * Create a MULTIPLY rule for multiplication
     *
     * @param array $values Values to multiply
     * @return \RuleFlow\Rule\Math\MultiplyRule
     */
    public static function multiply(array $values): MultiplyRule
    {
        return new MultiplyRule($values);
    }

    /**
     * Create a DIVIDE rule for division
     *
     * @param mixed $dividend Dividend (numerator)
     * @param mixed $divisor Divisor (denominator)
     * @return \RuleFlow\Rule\Math\DivideRule
     */
    public static function divide(mixed $dividend, mixed $divisor): DivideRule
    {
        return new DivideRule($dividend, $divisor);
    }

    /**
     * Create a MODULO rule for modulo operation
     *
     * @param mixed $dividend Dividend
     * @param mixed $divisor Divisor
     * @return \RuleFlow\Rule\Math\ModuloRule
     */
    public static function modulo(mixed $dividend, mixed $divisor): ModuloRule
    {
        return new ModuloRule($dividend, $divisor);
    }

    /**
     * Create an ALL rule for checking if all elements in a collection satisfy a condition
     *
     * @param mixed $collection Collection to check
     * @param mixed $condition Condition to evaluate
     * @return \RuleFlow\Rule\Logic\AllRule
     */
    public static function all(mixed $collection, mixed $condition): AllRule
    {
        return new AllRule($collection, $condition);
    }

    /**
     * Create a SOME rule for checking if any element in a collection satisfies a condition
     *
     * @param mixed $collection Collection to check
     * @param mixed $condition Condition to evaluate
     * @return \RuleFlow\Rule\Logic\SomeRule
     */
    public static function some(mixed $collection, mixed $condition): SomeRule
    {
        return new SomeRule($collection, $condition);
    }

    /**
     * Create a none rule
     *
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|array $collection Collection to check
     * @param \RuleFlow\Rule\JsonLogicRuleInterface|array $condition Condition to evaluate
     * @return \RuleFlow\Rule\Logic\NoneRule Rule
     */
    public static function none(JsonLogicRuleInterface|array $collection, JsonLogicRuleInterface|array $condition): NoneRule
    {
        return new NoneRule($collection, $condition);
    }

    /**
     * Create a between rule (exclusive) where min < value < max
     *
     * @param mixed $min Minimum value (exclusive)
     * @param mixed $value Value to check
     * @param mixed $max Maximum value (exclusive)
     * @return \RuleFlow\Rule\Comparison\LessThanRule
     */
    public static function between(mixed $min, mixed $value, mixed $max): LessThanRule
    {
        return new LessThanRule($min, $value, $max);
    }

    /**
     * Create a between rule (inclusive) where min <= value <= max
     *
     * @param mixed $min Minimum value (inclusive)
     * @param mixed $value Value to check
     * @param mixed $max Maximum value (inclusive)
     * @return \RuleFlow\Rule\Comparison\LessThanOrEqualRule
     */
    public static function betweenInclusive(mixed $min, mixed $value, mixed $max): LessThanOrEqualRule
    {
        return new LessThanOrEqualRule($min, $value, $max);
    }
}
