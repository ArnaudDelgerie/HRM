<?php

namespace App\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Symfony\Component\Workflow\Registry;

class WorkflowExpression implements ExpressionFunctionProviderInterface
{
    private $workflowRegistry;

    public function __construct(Registry $workflowRegistry)
    {
        $this->workflowRegistry = $workflowRegistry;
    }

    public function getFunctions(): array
    {
        return [
            new ExpressionFunction('workflow_can', function ($subject, $transition) {
                return sprintf('$this->workflowRegistry->get(%1$s)->can(%1$s, %2$s)', $subject, $transition);
            }, function (array $variables, $subject, $transition) {
                $workflow = $this->workflowRegistry->get($subject);
                return $workflow->can($subject, $transition);
            }),
        ];
    }
}

