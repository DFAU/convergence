<?php declare(strict_types=1);


namespace DFAU\Convergence\Schema;


use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class ExpressionIdentifier implements Identifier
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

    public function determineIdentity(array $resource, string $key) : string
    {
        return (string)$this->expressionLanguage->evaluate(
            $this->expression,
            [
                'resource' => $resource,
                'key' => $key
            ]
        );
    }
}
