<?php declare(strict_types=1);


namespace DFAU\Convergence\Schema;


final class IntraGraphResourceRelation implements ResourceRelation
{

    /**
     * @var ResourceQualifier
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

    /**
     * @var OrderedRelationQualifier
     */
    protected $orderedRelationQualifier;

    public function __construct(
        ResourceQualifier $subjectQualifier,
        ReferenceList $referenceList,
        Identifier $asIsResourceIdentifier,
        ?Identifier $toBeResourceIdentifier = null,
        ?OrderedRelationQualifier $orderedRelationQualifier = null
    )
    {
        $this->subjectQualifier = $subjectQualifier;
        $this->referenceList = $referenceList;
        $this->asIsResourceIdentifier = $asIsResourceIdentifier;
        $this->toBeResourceIdentifier = $toBeResourceIdentifier ?? $asIsResourceIdentifier;
        $this->orderedRelationQualifier = $orderedRelationQualifier;
    }

    public function isSubjectQualified(array $resource, string $key) : bool
    {
        return $this->subjectQualifier->resourceIsQualified($resource, $key);
    }

    public function getAvailableReferencePredicates(array $resource) : array
    {
        return $this->referenceList->getAvailablePredicates($resource);
    }

    public function getReferencesFromResource(array $resource, string $predicate = ReferenceList::DEFAULT_REFERENCE_PREDICATE) : array
    {
        return $this->referenceList->getReferencesFromResource($resource, $predicate);
    }

    public function applyReferencesToResource(array $relationResources, array $references, array $resource, string $predicate = ReferenceList::DEFAULT_REFERENCE_PREDICATE) : array
    {
        return $this->referenceList->applyReferencesToResource($relationResources, $references, $resource, $predicate);
    }

    public function determineAsIsResourceIdentifier(array $resource, string $key): string
    {
        return $this->asIsResourceIdentifier->determineIdentity($resource, $key);
    }

    public function determineToBeResourceIdentifier(array $resource, string $key): string
    {
        return $this->toBeResourceIdentifier->determineIdentity($resource, $key);
    }

    public function isResourceRelationOrdered(array $resource, string $predicate): bool
    {
        if ($this->orderedRelationQualifier) {
            return $this->orderedRelationQualifier->resourceRelationIsOrdered($resource, $predicate);
        }
        return true;
    }
}
