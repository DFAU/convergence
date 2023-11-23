<?php declare(strict_types=1);


namespace DFAU\Convergence\Schema;


use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use DFAU\Convergence\ExpressionLanguage\DefaultFunctionsProvider;

class ExpressionQualifier implements ResourceQualifier, PropertyQualifier, OrderedRelationQualifier
{

    /**
     * @var string
     */
    protected $expression;

    /**
     * @var ExpressionLanguage
     */
    protected $expressionLanguage;

    public function __construct(string $expression)
    {
        $this->expression = $expression;
        $this->expressionLanguage = (new ExpressionLanguage(null, [ new DefaultFunctionsProvider() ]));
    }

    public function resourceIsQualified(array $resource, string $key) : bool
    {
        return (bool)$this->expressionLanguage->evaluate(
            $this->expression,
            [
                'resource' => $resource,
                'key' => $key
            ]
        );
    }

    public function propertyIsQualified($value, string $key) : bool
    {
        return (bool)$this->expressionLanguage->evaluate(
            $this->expression,
            [
                'value' => $value,
                'key' => $key
            ]
        );
    }

    public function resourceRelationIsOrdered(array $resource, string $predicate): bool
    {
        return (bool)$this->expressionLanguage->evaluate(
            $this->expression,
            [
                'resource' => $resource,
                'predicate' => $predicate
            ]
        );
    }
}
