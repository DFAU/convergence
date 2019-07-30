<?php declare(strict_types=1);


namespace DFAU\Convergence\Schema;


final class ResourcePropertiesExtractor
{

    /**
     * @var Qualifier
     */
    protected $subjectQualifier;

    /**
     * @var PropertyList
     */
    protected $propertyList;

    /**
     * @var Identifier
     */
    protected $asIsResourceIdentifier;

    /**
     * @var Identifier
     */
    protected $toBeResourceIdentifier;

    public function __construct(
        Qualifier $subjectQualifier,
        PropertyList $propertyList)
    {
        $this->subjectQualifier = $subjectQualifier;
        $this->propertyList = $propertyList;
    }

    public function isSubjectQualified(array $resource, string $key) : bool
    {
        return $this->subjectQualifier->resourceIsQualified($resource, $key);
    }

    public function getPropertiesFromResource(array $resource): array
    {
        return $this->propertyList->getPropertiesFromResource($resource);
    }

}
