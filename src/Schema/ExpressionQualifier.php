<?php declare(strict_types=1);


namespace DFAU\Convergence\Schema;


use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class ExpressionQualifier implements Qualifier
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
        $this->expressionLanguage = (new ExpressionLanguage());
    }

    public function resourceIsQualified(array $resource, string $key) : bool
    {
        return (bool)$this->expressionLanguage->evaluate(
            $this->expression,
            [
                'resource' => (object)$resource, // Casting to object here, so we can work with properties inside expressions
                'key' => $key
            ]
        );
    }
}
