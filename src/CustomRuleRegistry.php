<?php
declare(strict_types=1);

namespace RuleFlow;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;

/**
 * Custom Rule Registry
 *
 * Registry for custom rule types and operators following the pattern
 * from the examples provided
 */
class CustomRuleRegistry
{
    /**
     * Instance of the registry
     *
     * @var \RuleFlow\CustomRuleRegistry|null
     */
    protected static ?CustomRuleRegistry $instance = null;

    /**
     * Map of JSON Logic operators to class names
     *
     * @var array<string, string>
     */
    protected array $operatorMap = [];

    /**
     * Map of rule instances by operator
     *
     * @var array<string, \RuleFlow\CustomRuleInterface>
     */
    protected array $ruleInstances = [];

    /**
     * Get singleton instance
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Register a custom rule class
     *
     * @param string $className Custom rule class name
     * @return static
     * @throws \InvalidArgumentException If class doesn't implement CustomRuleInterface
     */
    public function register(string $className): static
    {
        if (!class_exists($className)) {
            throw new InvalidArgumentException("Rule class '{$className}' does not exist");
        }

        if (!is_subclass_of($className, CustomRuleInterface::class)) {
            throw new InvalidArgumentException("Rule class '{$className}' must implement CustomRuleInterface");
        }

        $reflectionClass = new ReflectionClass($className);

        try {
            $operator = $reflectionClass->getStaticPropertyValue('operator');
        } catch (ReflectionException $e) {
            $tempInstance = new $className();
            $operator = $tempInstance->getOperator();
        }

        $this->operatorMap[$operator] = $className;

        return $this;
    }

    /**
     * Get the class name for a JSON Logic operator
     *
     * @param string $operator JSON Logic operator
     * @return string|null Class name or null if not found
     */
    public function getClassForOperator(string $operator): ?string
    {
        return $this->operatorMap[$operator] ?? null;
    }

    /**
     * Get custom rule instance by operator
     *
     * @param string $operator Operator name
     * @return \RuleFlow\CustomRuleInterface|null Rule instance or null if not found
     */
    public function getCustomRule(string $operator): ?CustomRuleInterface
    {
        if (!isset($this->operatorMap[$operator])) {
            return null;
        }

        if (!isset($this->ruleInstances[$operator])) {
            $className = $this->operatorMap[$operator];
            $this->ruleInstances[$operator] = new $className();
        }

        return $this->ruleInstances[$operator];
    }

    /**
     * Check if a custom rule is registered
     *
     * @param string $operator Operator name
     * @return bool True if rule is registered
     */
    public function hasOperator(string $operator): bool
    {
        return isset($this->operatorMap[$operator]);
    }

    /**
     * Get all registered operators
     *
     * @return array<string> Array of operator names
     */
    public function getOperators(): array
    {
        return array_keys($this->operatorMap);
    }

    /**
     * Create a rule instance from operator and data
     *
     * @param string $operator Rule operator
     * @param mixed $data Rule data
     * @return \RuleFlow\CustomRuleInterface Rule instance
     * @throws \InvalidArgumentException If the rule operator is not registered
     */
    public function create(string $operator, mixed $data): CustomRuleInterface
    {
        $className = $this->getClassForOperator($operator);

        if ($className === null) {
            throw new InvalidArgumentException("Custom rule operator '{$operator}' is not registered");
        }

        if (method_exists($className, 'fromArray')) {
            return $className::fromArray($data);
        }

        return new $className($data);
    }

    /**
     * Clear all registered custom rules
     *
     * @return void
     */
    public function clear(): void
    {
        $this->operatorMap = [];
        $this->ruleInstances = [];
    }

    /**
     * Static register method
     *
     * @param string $className Custom rule class name
     * @return void
     */
    public static function registerRule(string $className): void
    {
        static::getInstance()->register($className);
    }

    /**
     * Static method to check if operator is registered
     *
     * @param string $operator Operator name
     * @return bool True if registered
     */
    public static function hasCustomRule(string $operator): bool
    {
        return static::getInstance()->hasOperator($operator);
    }

    /**
     * Static method to get custom rule
     *
     * @param string $operator Operator name
     * @return \RuleFlow\CustomRuleInterface|null Rule instance or null
     */
    public static function getRule(string $operator): ?CustomRuleInterface
    {
        return static::getInstance()->getCustomRule($operator);
    }
}
