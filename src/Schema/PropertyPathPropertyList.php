<?php declare(strict_types=1);


namespace DFAU\Convergence\Schema;


use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class PropertyPathPropertyList implements PropertyList
{

    /**
     * @var string
     */
    protected $listDelimiter;

    /**
     * @var PropertyAccessor
     */
    protected $propertyAccessor;

    /**
     * @var string
     */
    protected $propertyPath;

    public function __construct(string $propertyPath)
    {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->propertyPath = $propertyPath;
    }

    public function getPropertiesFromResource(array $resource): array
    {
        // Casting to object here, so we can work with properties inside expressions
        return (array)$this->propertyAccessor->getValue((object) ['resource' => $resource], $this->propertyPath);
    }

    public function applyPropertiesToResource(array $properties, array $resource): array
    {
        $object = (object) ['resource' => $resource];
        $this->propertyAccessor->setValue($object, $this->propertyPath, $properties);
        return $object->resource;
    }
}
