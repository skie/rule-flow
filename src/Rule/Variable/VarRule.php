<?php
declare(strict_types=1);

namespace RuleFlow\Rule\Variable;

use RuleFlow\Rule\AbstractJsonLogicRule;

/**
 * Var Rule
 *
 * JsonLogic rule for accessing variables
 */
class VarRule extends AbstractJsonLogicRule
{
    /**
     * The variable path to access
     *
     * @var array|string
     */
    protected string|array $path;

    /**
     * Default value if variable doesn't exist
     *
     * @var mixed
     */
    protected mixed $defaultValue = null;

    /**
     * Constructor
     *
     * @param array|string $path Variable path to access
     * @param mixed $defaultValue Default value if variable doesn't exist
     */
    public function __construct(string|array $path, mixed $defaultValue = null)
    {
        $this->operator = 'var';
        $this->path = $path;
        $this->defaultValue = $defaultValue;
    }

    /**
     * Get the variable path
     *
     * @return array|string Variable path
     */
    public function getPath(): string|array
    {
        return $this->path;
    }

    /**
     * Get the default value
     *
     * @return mixed Default value
     */
    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    /**
     * @inheritDoc
     */
    protected function getOperands(): mixed
    {
        if ($this->defaultValue !== null) {
            return [$this->path, $this->valueToArray($this->defaultValue)];
        }

        return $this->path;
    }
}
