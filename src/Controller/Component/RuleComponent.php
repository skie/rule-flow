<?php
declare(strict_types=1);

namespace RuleFlow\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\Table;
use RuleFlow\Validation\ValidationRuleConverter;

/**
 * Rule Component
 *
 * Provides functionality to configure form data with JSON logic rules
 * converted from CakePHP validation rules.
 */
class RuleComponent extends Component
{
    /**
     * Configure form data with JSON logic rules from table validator
     *
     * @param \Cake\ORM\Table $table Table instance
     * @param string $validatorName Validator name (default: 'default')
     * @return void
     */
    public function configureFormRules(Table $table, string $validatorName = 'default'): void
    {
        $validator = $table->getValidator($validatorName);
        $converter = new ValidationRuleConverter();
        $jsonLogic = $converter->convertValidator($validator);
        $this->getController()->set('jsonLogic', $jsonLogic);
    }
}
