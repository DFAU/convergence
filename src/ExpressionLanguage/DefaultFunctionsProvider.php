<?php declare(strict_types=1);

namespace DFAU\Convergence\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class DefaultFunctionsProvider implements ExpressionFunctionProviderInterface
{

    /**
     * @return ExpressionFunction[] An array of Function instances
     */
    public function getFunctions(): array
    {
        return [
            $this->getTraverseArrayFunction(),
        ];
    }

    public function getTraverseArrayFunction(): ExpressionFunction
    {
        return new ExpressionFunction('traverse', static function () {
            // Not implemented, we only use the evaluator
        }, static function ($arguments, $array, $path) {
            if (!is_array($array) || !is_string($path) || $path === '') {
                return '';
            }
            $path = '[' . str_replace('/', '][', $path) . ']';
            $propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()->disableExceptionOnInvalidIndex()->getPropertyAccessor();
            return $propertyAccessor->getValue($array, $path);
        });
    }

}
