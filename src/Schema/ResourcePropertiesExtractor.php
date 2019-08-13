<?php declare(strict_types=1);


namespace DFAU\Convergence\Schema;


final class ResourcePropertiesExtractor
{

    /**
     * @var ResourceQualifier
     */
    protected $subjectQualifier;

    /**
     * @var PropertyList
     */
    protected $propertyList;

    /**
     * @var ResourceQualifier
     */
    protected $propertyQualifier;

    /**
     * @var Identifier
     */
    protected $asIsResourceIdentifier;

    /**
     * @var Identifier
     */
    protected $toBeResourceIdentifier;

    public function __construct(
        ResourceQualifier $subjectQualifier,
        PropertyList $propertyList,
        PropertyQualifier $propertyQualifier = null
    )
    {
        $this->subjectQualifier = $subjectQualifier;
        $this->propertyList = $propertyList;
        $this->propertyQualifier = $propertyQualifier;
    }

    public function isSubjectQualified(array $resource, string $key) : bool
    {
        return $this->subjectQualifier->resourceIsQualified($resource, $key);
    }

    public function getPropertiesFromResource(array $resource): array
    {
        $properties = $this->propertyList->getPropertiesFromResource($resource);

        if ($this->propertyQualifier !== null) {
            $properties = array_filter($properties, [$this->propertyQualifier, 'propertyIsQualified'], ARRAY_FILTER_USE_BOTH);
        }

        return $properties;
    }

    public function applyPropertiesToResource(array $properties, array $resource) : array
    {
        return $this->propertyList->applyPropertiesToResource($properties, $resource);
    }

}
