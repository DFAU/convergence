<?php declare(strict_types=1);


namespace DFAU\Convergence\Schema;


final class IntraGraphResourceRelation implements ResourceRelation
{

    /**
     * @var Qualifier
     */
    protected $subjectQualifier;

    /**
     * @var ReferenceList
     */
    protected $referenceList;

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
        ReferenceList $referenceList,
        Identifier $asIsResourceIdentifier,
        ?Identifier $toBeResourceIdentifier = null)
    {
        $this->subjectQualifier = $subjectQualifier;
        $this->referenceList = $referenceList;
        $this->asIsResourceIdentifier = $asIsResourceIdentifier;
        $this->toBeResourceIdentifier = $toBeResourceIdentifier ?? $asIsResourceIdentifier;
    }

    public function isSubjectQualified(array $resource, string $key) : bool
    {
        return $this->subjectQualifier->resourceIsQualified($resource, $key);
    }

    public function getReferencesFromResource(array $resource) : array
    {
        return $this->referenceList->getReferencesFromResource($resource);
    }

    public function applyReferencesToResource(array $references, array $resource) : array
    {
        return $this->referenceList->applyReferencesToResource($references, $resource);
    }

    public function determineAsIsResourceIdentifier(array $resource, string $key): string
    {
        return $this->asIsResourceIdentifier->determineIdentity($resource, $key);
    }


    public function determineToBeResourceIdentifier(array $resource, string $key): string
    {
        return $this->toBeResourceIdentifier->determineIdentity($resource, $key);
    }
}
