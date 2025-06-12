<?php
declare(strict_types=1);

namespace RuleFlow\Rule\Collection;

use RuleFlow\Rule\AbstractJsonLogicRule;

/**
 * Missing Rule
 *
 * JsonLogic rule for checking which keys are missing from data
 */
class MissingRule extends AbstractJsonLogicRule
{
    /**
     * The keys to check
     *
     * @var array<string|int>
     */
    protected array $keys = [];

    /**
     * Constructor
     *
     * @param array<string|int> $keys Keys to check for
     */
    public function __construct(array $keys)
    {
        $this->operator = 'missing';
        $this->keys = $keys;
    }

    /**
     * Get the keys being checked
     *
     * @return array<string|int> Keys
     */
    public function getKeys(): array
    {
        return $this->keys;
    }

    /**
     * Set the keys to check
     *
     * @param array<string|int> $keys Keys to check
     * @return $this
     */
    public function setKeys(array $keys)
    {
        $this->keys = $keys;

        return $this;
    }

    /**
     * Add a key to check
     *
     * @param string|int $key Key to add
     * @return $this
     */
    public function addKey(string|int $key)
    {
        $this->keys[] = $key;

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function getOperands(): mixed
    {
        return $this->valueToArray($this->keys);
    }
}
