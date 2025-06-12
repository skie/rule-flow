<?php
declare(strict_types=1);

namespace RuleFlow\Rule\Collection;

use RuleFlow\Rule\AbstractJsonLogicRule;

/**
 * MissingSome Rule
 *
 * JsonLogic rule for checking if at least N of M keys are present
 */
class MissingSomeRule extends AbstractJsonLogicRule
{
    /**
     * Minimum number of keys required
     *
     * @var int
     */
    protected int $minRequired;

    /**
     * The keys to check
     *
     * @var array<string|int>
     */
    protected array $keys = [];

    /**
     * Constructor
     *
     * @param int $minRequired Minimum number of keys required
     * @param array<string|int> $keys Keys to check
     */
    public function __construct(int $minRequired, array $keys)
    {
        $this->operator = 'missing_some';
        $this->minRequired = $minRequired;
        $this->keys = $keys;
    }

    /**
     * Get the minimum number of keys required
     *
     * @return int Minimum required
     */
    public function getMinRequired(): int
    {
        return $this->minRequired;
    }

    /**
     * Set the minimum number of keys required
     *
     * @param int $minRequired Minimum required
     * @return $this
     */
    public function setMinRequired(int $minRequired)
    {
        $this->minRequired = $minRequired;

        return $this;
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
     * @inheritDoc
     */
    protected function getOperands(): mixed
    {
        return [
            $this->minRequired,
            $this->valueToArray($this->keys),
        ];
    }
}
