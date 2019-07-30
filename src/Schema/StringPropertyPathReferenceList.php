<?php declare(strict_types=1);


namespace DFAU\Convergence\Schema;


use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class StringPropertyPathReferenceList implements ReferenceList
{

    const DELIMITER_COMMA = ',';

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

    public function __construct(string $propertyPath, $listDelimiter = self::DELIMITER_COMMA)
    {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->propertyPath = $propertyPath;
        $this->listDelimiter = $listDelimiter;
    }

    public function getReferencesFromResource(array $resource): array
    {
        $referenceList = $this->propertyAccessor->getValue((object) ['resource' => $resource], $this->propertyPath);
        $references = explode($this->listDelimiter, (string)$referenceList);
        return array_map('trim', $references);
    }

    public function applyReferencesToResource(array $references, array $resource) : array
    {
        $object = (object) ['resource' => $resource];
        $referenceList = implode($this->listDelimiter, $references);
        $this->propertyAccessor->setValue($object, $this->propertyPath, $referenceList);
        return (array)$object->resource;
    }
}
