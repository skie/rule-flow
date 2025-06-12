<?php
declare(strict_types=1);

namespace RuleFlow\View\Helper;

/**
 * Rule Logic Trait
 *
 * Provides functionality to append JSON logic rules to the page
 * as a script block for client-side processing.
 */
trait RuleLogicTrait
{
    /**
     * Before render callback to append JSON logic rules script
     *
     * @param string $viewFile View file being rendered
     * @return void
     */
    public function beforeRender(string $viewFile): void
    {
        $this->appendJsonLogic();
    }

    /**
     * Append JSON Logic script block to the page
     *
     * @return void
     */
    public function appendJsonLogic(): void
    {
        $view = $this->getView();
        $jsonLogic = $view->get('jsonLogic');

        if ($jsonLogic !== null) {
            $scriptBlock = sprintf(
                '<script id="json-logic-rules" type="application/json">%s</script>',
                json_encode($jsonLogic),
            );

            $view->append('script', $scriptBlock);
        }
    }
}
