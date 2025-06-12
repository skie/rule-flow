<?php
declare(strict_types=1);

namespace RuleFlow;

use ArrayAccess;
use InvalidArgumentException;

/**
 * JsonLogic Engine
 *
 * Implementation of the rule engine using JsonLogic syntax
 */
class JsonLogicEngine
{
    /**
     * Rule evaluator instance
     *
     * @var \RuleFlow\JsonLogicEvaluator
     */
    protected JsonLogicEvaluator $evaluator;

    /**
     * Constructor
     *
     * @param \RuleFlow\JsonLogicEvaluator|null $evaluator Rule evaluator
     */
    public function __construct(?JsonLogicEvaluator $evaluator = null)
    {
        $this->evaluator = $evaluator ?? new JsonLogicEvaluator();
    }

    /**
     * @inheritDoc
     */
    public function getEvaluator(): RuleEvaluatorInterface
    {
        return $this->evaluator;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'jsonlogic';
    }

    /**
     * Create a rule using the JsonLogic syntax
     *
     * @param string $type Rule type
     * @param array $options Rule options
     * @return array Rule in JsonLogic format
     */
    public function createRule(string $type, array $options = []): array
    {
        switch ($type) {
            case 'equals':
                return $this->createEqualsRule($options);
            case 'notEquals':
                return $this->createNotEqualsRule($options);
            case 'greaterThan':
                return $this->createGreaterThanRule($options);
            case 'lessThan':
                return $this->createLessThanRule($options);
            case 'greaterThanOrEqual':
                return $this->createGreaterThanOrEqualRule($options);
            case 'lessThanOrEqual':
                return $this->createLessThanOrEqualRule($options);
            case 'contains':
                return $this->createContainsRule($options);
            case 'startsWith':
                return $this->createStartsWithRule($options);
            case 'endsWith':
                return $this->createEndsWithRule($options);
            case 'in':
                return $this->createInRule($options);
            case 'and':
                return $this->createAndRule($options);
            case 'or':
                return $this->createOrRule($options);
            case 'not':
                return $this->createNotRule($options);
            default:
                throw new InvalidArgumentException(sprintf('Unsupported rule type: %s', $type));
        }
    }

    /**
     * Evaluate a rule against an entity
     *
     * @param array $rule Rule to evaluate
     * @param \ArrayAccess|array $entity Entity to evaluate against
     * @return bool Result of evaluation
     */
    public function evaluate(array $rule, ArrayAccess|array $entity): bool
    {
        return $this->evaluator->evaluate($rule, $entity);
    }

    /**
     * Create an equals rule
     *
     * @param array $options Rule options
     * @return array Rule in JsonLogic format
     */
    protected function createEqualsRule(array $options): array
    {
        $field = $options['field'] ?? null;
        $value = $options['value'] ?? null;

        if ($field === null) {
            throw new InvalidArgumentException('Field option is required for equals rule');
        }

        return ['==' => [['var' => $field], $value]];
    }

    /**
     * Create a not equals rule
     *
     * @param array $options Rule options
     * @return array Rule in JsonLogic format
     */
    protected function createNotEqualsRule(array $options): array
    {
        $field = $options['field'] ?? null;
        $value = $options['value'] ?? null;

        if ($field === null) {
            throw new InvalidArgumentException('Field option is required for notEquals rule');
        }

        return ['!=' => [['var' => $field], $value]];
    }

    /**
     * Create a greater than rule
     *
     * @param array $options Rule options
     * @return array Rule in JsonLogic format
     */
    protected function createGreaterThanRule(array $options): array
    {
        $field = $options['field'] ?? null;
        $value = $options['value'] ?? null;

        if ($field === null) {
            throw new InvalidArgumentException('Field option is required for greaterThan rule');
        }

        return ['>' => [['var' => $field], $value]];
    }

    /**
     * Create a less than rule
     *
     * @param array $options Rule options
     * @return array Rule in JsonLogic format
     */
    protected function createLessThanRule(array $options): array
    {
        $field = $options['field'] ?? null;
        $value = $options['value'] ?? null;

        if ($field === null) {
            throw new InvalidArgumentException('Field option is required for lessThan rule');
        }

        return ['<' => [['var' => $field], $value]];
    }

    /**
     * Create a greater than or equal rule
     *
     * @param array $options Rule options
     * @return array Rule in JsonLogic format
     */
    protected function createGreaterThanOrEqualRule(array $options): array
    {
        $field = $options['field'] ?? null;
        $value = $options['value'] ?? null;

        if ($field === null) {
            throw new InvalidArgumentException('Field option is required for greaterThanOrEqual rule');
        }

        return ['>=' => [['var' => $field], $value]];
    }

    /**
     * Create a less than or equal rule
     *
     * @param array $options Rule options
     * @return array Rule in JsonLogic format
     */
    protected function createLessThanOrEqualRule(array $options): array
    {
        $field = $options['field'] ?? null;
        $value = $options['value'] ?? null;

        if ($field === null) {
            throw new InvalidArgumentException('Field option is required for lessThanOrEqual rule');
        }

        return ['<=' => [['var' => $field], $value]];
    }

    /**
     * Create a contains rule
     *
     * @param array $options Rule options
     * @return array Rule in JsonLogic format
     */
    protected function createContainsRule(array $options): array
    {
        $field = $options['field'] ?? null;
        $value = $options['value'] ?? null;

        if ($field === null) {
            throw new InvalidArgumentException('Field option is required for contains rule');
        }

        return ['contains' => [['var' => $field], $value]];
    }

    /**
     * Create a startsWith rule
     *
     * @param array $options Rule options
     * @return array Rule in JsonLogic format
     */
    protected function createStartsWithRule(array $options): array
    {
        $field = $options['field'] ?? null;
        $value = $options['value'] ?? null;

        if ($field === null) {
            throw new InvalidArgumentException('Field option is required for startsWith rule');
        }

        return ['startsWith' => [['var' => $field], $value]];
    }

    /**
     * Create an endsWith rule
     *
     * @param array $options Rule options
     * @return array Rule in JsonLogic format
     */
    protected function createEndsWithRule(array $options): array
    {
        $field = $options['field'] ?? null;
        $value = $options['value'] ?? null;

        if ($field === null) {
            throw new InvalidArgumentException('Field option is required for endsWith rule');
        }

        return ['endsWith' => [['var' => $field], $value]];
    }

    /**
     * Create an in rule
     *
     * @param array $options Rule options
     * @return array Rule in JsonLogic format
     */
    protected function createInRule(array $options): array
    {
        $field = $options['field'] ?? null;
        $value = $options['value'] ?? null;

        if ($field === null) {
            throw new InvalidArgumentException('Field option is required for in rule');
        }

        return ['in' => [['var' => $field], $value]];
    }

    /**
     * Create an AND rule
     *
     * @param array $options Rule options
     * @return array Rule in JsonLogic format
     */
    protected function createAndRule(array $options): array
    {
        $rules = $options['rules'] ?? [];

        if (empty($rules)) {
            throw new InvalidArgumentException('Rules option is required for AND rule');
        }

        return ['and' => $rules];
    }

    /**
     * Create an OR rule
     *
     * @param array $options Rule options
     * @return array Rule in JsonLogic format
     */
    protected function createOrRule(array $options): array
    {
        $rules = $options['rules'] ?? [];

        if (empty($rules)) {
            throw new InvalidArgumentException('Rules option is required for OR rule');
        }

        return ['or' => $rules];
    }

    /**
     * Create a NOT rule
     *
     * @param array $options Rule options
     * @return array Rule in JsonLogic format
     */
    protected function createNotRule(array $options): array
    {
        $rule = $options['rule'] ?? null;

        if (empty($rule)) {
            throw new InvalidArgumentException('Rule option is required for NOT rule');
        }

        return ['!' => $rule];
    }

    /**
     * Parse a rule from standard format to engine-specific format
     *
     * @param array|null $rule Rule in standard format
     * @return mixed Rule in engine-specific format
     */
    public function parseRule(?array $rule): mixed
    {
        // TODO: Implement parseRule() method.
        return null;
    }

    /**
     * Format a rule from engine-specific format to standard format
     *
     * @param mixed $rule Rule in engine-specific format
     * @return array|null Rule in standard format
     */
    public function formatRule(mixed $rule): ?array
    {
        // TODO: Implement formatRule() method.
        return null;
    }
}
