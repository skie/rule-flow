<?php
declare(strict_types=1);

namespace RuleFlow\View\Helper;

use Cake\View\Helper\FormHelper as BaseFormHelper;

/**
 * Form Helper
 *
 * Extended FormHelper that automatically processes JSON logic rules
 * and adds data attributes for client-side validation.
 */
class FormHelper extends BaseFormHelper
{
    use RuleLogicTrait;

    /**
     * Creates an HTML form element with automatic JSON logic rule processing
     *
     * @param mixed $context The context for which the form is being defined
     * @param array<string, mixed> $options An array of html attributes and options
     * @return string An formatted opening FORM tag
     */
    public function create(mixed $context = null, array $options = []): string
    {
        $options['data-json-logic'] = '#json-logic-rules';

        return parent::create($context, $options);
    }
}
