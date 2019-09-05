<?php declare(strict_types=1);


namespace DFAU\Convergence;


use DFAU\Convergence\Schema\IntraGraphResourceRelation;
use DFAU\Convergence\Schema\ResourcePropertiesExtractor;

class ResourceMap
{

    const RESOURCE_SIDE_AS_IS = 'asIs';
    const RESOURCE_SIDE_TO_BE = 'toBe';

    const COMPARISON_RESOURCES_TO_ADD = 'resourcesToAdd';
    const COMPARISON_RESOURCES_TO_REMOVE = 'resourcesToRemove';
    const COMPARISON_RESOURCES_TO_UPDATE = 'resourcesToUpdate';

    const COMPARISON_PROPERTIES_TO_ADD = 'propertiesToAdd';
    const COMPARISON_PROPERTIES_TO_REMOVE = 'propertiesToRemove';
    const COMPARISON_PROPERTIES_TO_UPDATE = 'propertiesToUpdate';

    /**
     * @var array<string, array>
     */
    protected $resourceMap;

    /**
     * @var \SplObjectStorage<IntraGraphResourceRelation>
     */
    protected $relationMap;

    /**
     * @var Schema
     */
    protected $schema;


    public function __construct()
    {
        $this->resourceMap = [];
        $this->relationMap = new \SplObjectStorage();
    }

    public function compare(ResourceMap $toBeResourceMap): array
    {
        if ($toBeResourceMap->schema !== $this->schema) {
            throw new \InvalidArgumentException('Resource maps with distinct schemas cannot be compared.', 1562592226);
        }

        [ $identifierToAdd, $identifierToRemove, $identifierToUpdate ] = $this->compareLists(
            array_keys($toBeResourceMap->resourceMap),
            array_keys($this->resourceMap)
        );

        $resourcesToAdd = array_map(function &($identifier) use ($toBeResourceMap) {
            return $toBeResourceMap->resourceMap[$identifier];
        }, $identifierToAdd);

        $resourcesToRemove = array_map(function &($identifier) {
            return $this->resourceMap[$identifier];
        }, $identifierToRemove);

        $resourcesToUpdate = array_combine($identifierToUpdate, array_map(function ($identifier) use ($toBeResourceMap) {
            $toBeResource = &$toBeResourceMap->resourceMap[$identifier];
            $asIsResource = &$this->resourceMap[$identifier];

            if ($propertiesExtractor = $this->findSuitablePropertiesExtractor(array_merge($toBeResource, $asIsResource), $identifier)) {
                $toBeResourceProperties = $propertiesExtractor->getPropertiesFromResource($toBeResource);
                $asIsResourceProperties = $propertiesExtractor->getPropertiesFromResource($asIsResource);
            } else {
                $toBeResourceProperties = $asIsResourceProperties = [];
            }

            [ $propertyNamesToAdd, $propertyNamesToRemove, $propertyNamesToUpdate ] = $this->compareLists(
                array_keys($toBeResourceProperties),
                array_keys($asIsResourceProperties)
            );

            $propertiesToAdd = array_combine($propertyNamesToAdd, array_map(function($propertyName) use ($toBeResourceProperties) {
                return $toBeResourceProperties[$propertyName];
            }, $propertyNamesToAdd));

            $propertiesToRemove = array_fill_keys($propertyNamesToRemove, null);

            $propertiesToUpdate = [];

            foreach ($propertyNamesToUpdate as $propertyName) {
                if (isset($propertiesToUpdate[$propertyName]) || $toBeResourceProperties[$propertyName] == $asIsResourceProperties[$propertyName]) {
                    continue;
                }
                $propertiesToUpdate[$propertyName] = $toBeResourceProperties[$propertyName];
            }

            if ($propertiesExtractor) {
                $propertiesToAdd = $propertiesToAdd ? $propertiesExtractor->applyPropertiesToResource($propertiesToAdd, []) : $propertiesToAdd;
                $propertiesToRemove = $propertiesToRemove ? $propertiesExtractor->applyPropertiesToResource($propertiesToRemove, []) : $propertiesToRemove;
                $propertiesToUpdate = $propertiesToUpdate ? $propertiesExtractor->applyPropertiesToResource($propertiesToUpdate, []) : $propertiesToUpdate;
            }

            /** @var IntraGraphResourceRelation $intraGraphRelation */
            foreach ($this->relationMap as $intraGraphRelation) {
                $mergedResource = array_merge($toBeResource, $asIsResource);
                if (!$intraGraphRelation->isSubjectQualified($mergedResource, $identifier)) {
                    continue;
                }

                foreach ($intraGraphRelation->getAvailableReferencePredicates($mergedResource) as $predicate) {
                    $toBeRelationMaps = $this->resolveRelationIdentifiers($toBeResourceMap, $intraGraphRelation, $toBeResource, $predicate);
                    $asIsRelationMaps = $this->resolveRelationIdentifiers($this, $intraGraphRelation, $asIsResource, $predicate);

                    $toBeRelations = array_values(array_filter($toBeRelationMaps));
                    $asIsRelations = array_values(array_filter($asIsRelationMaps));

                    $comparisonFunction = $intraGraphRelation->isResourceRelationOrdered($mergedResource, $predicate) ? 'array_diff_assoc' : 'array_diff';

                    if ($comparisonFunction($toBeRelations, $asIsRelations) || $comparisonFunction($asIsRelations, $toBeRelations)) {
                        // TODO check whether it's right to provide the match identifiers here
                        $toBeRelationResources = array_combine($toBeRelations, array_map(function($resourceIdentifier) use($toBeResourceMap) {
                            return $toBeResourceMap->resourceMap[$resourceIdentifier];
                        }, $toBeRelations));
                        $propertiesToUpdate = $intraGraphRelation->applyReferencesToResource($toBeRelationResources, $toBeRelationMaps, $propertiesToUpdate, $predicate);
                    }
                }
            }

            return [
                static::COMPARISON_PROPERTIES_TO_ADD => $propertiesToAdd,
                static::COMPARISON_PROPERTIES_TO_REMOVE => $propertiesToRemove,
                static::COMPARISON_PROPERTIES_TO_UPDATE => $propertiesToUpdate,
                static::RESOURCE_SIDE_TO_BE => $toBeResource,
                static::RESOURCE_SIDE_AS_IS => $asIsResource
            ];
        }, $identifierToUpdate));

        return [
            static::COMPARISON_RESOURCES_TO_ADD => $resourcesToAdd,
            static::COMPARISON_RESOURCES_TO_REMOVE => $resourcesToRemove,
            static::COMPARISON_RESOURCES_TO_UPDATE => $resourcesToUpdate
        ];
    }

    protected function compareLists(array $toBeList, array $asIsList) : array
    {
        $entriesToAdd = array_diff($toBeList, $asIsList);
        $entriesToRemove = array_diff($asIsList, $toBeList);
        $commonEntries = array_diff($asIsList, $entriesToAdd, $entriesToRemove);

        return [$entriesToAdd, $entriesToRemove, $commonEntries];
    }

    protected function resolveRelationIdentifiers(ResourceMap $resourceMap, IntraGraphResourceRelation $intraGraphRelation, $resource, string $predicate): array
    {
        $references = $intraGraphRelation->getReferencesFromResource($resource, $predicate);
        return array_combine($references, array_map(function ($reference) use ($resourceMap, $intraGraphRelation) {
            return $resourceMap->relationMap[$intraGraphRelation][$reference] ?? null;
        }, $references));
    }

    protected function findSuitablePropertiesExtractor(array $resource, string $identifier): ?ResourcePropertiesExtractor
    {
        /** @var ResourcePropertiesExtractor $propertiesExtractor */
        foreach ($this->schema->getResourcePropertiesExtractors() as $propertiesExtractor) {
            if ($propertiesExtractor->isSubjectQualified($resource, $identifier)) {
                return $propertiesExtractor;
            }
        }
        return null;
    }


    static public function fromSchemaAndResources(Schema $schema, string $resourceSide, array $resources): self
    {
        if (!in_array($resourceSide, [static::RESOURCE_SIDE_AS_IS, static::RESOURCE_SIDE_TO_BE])) {
            throw new \InvalidArgumentException(
                'Resource side must be either ::RESOURCE_SIDE_AS_IS or ::RESOURCE_SIDE_TO_BE. "' . $resourceSide . '" has been given instead.',
                1562588992
            );
        }

        $identityMap = new static;
        $identityMap->schema = $schema;

        $interGraphRelations = $schema->getInterGraphRelations();
        foreach ($schema->getIntraGraphRelations() as $intraGraphRelation) {
            $identityMap->relationMap[$intraGraphRelation] = [];
        }

        foreach ($resources as $key => $resource) {
            $resourceIdentifier = static::addResourceToInterGraphMap($identityMap->resourceMap, $interGraphRelations, $resourceSide, $resource, (string)$key);

            /** @var IntraGraphResourceRelation $intraGraphRelation */
            foreach ($identityMap->relationMap as $intraGraphRelation) {
                $relationMap = $identityMap->relationMap[$intraGraphRelation];
                static::addResourceToIntraGraphRelationMap($relationMap, $resourceIdentifier, $intraGraphRelation, $resourceSide, $resource, (string)$key);
                $identityMap->relationMap[$intraGraphRelation] = $relationMap;
            }
        }

        return $identityMap;
    }

    static protected function addResourceToInterGraphMap(array &$resourcesByIdentifier, \SplObjectStorage $interGraphRelations, string $resourceSide, array $resource, string $key) : string
    {
        $identifier = '';
        foreach ($interGraphRelations as $interGraphRelation) {
            switch ($resourceSide) {
                case static::RESOURCE_SIDE_AS_IS:
                    $identifier = $interGraphRelation->determineAsIsResourceIdentifier($resource, $key);
                    break;
                case static::RESOURCE_SIDE_TO_BE:
                    $identifier = $interGraphRelation->determineToBeResourceIdentifier($resource, $key);
            }
            if (!empty($identifier)) {
                break;
            }
        }

        if (empty($identifier)) {
            throw new \InvalidArgumentException(
                'Cannot determine identity of the given resource.',
                1562576855
            );
        }

        if (isset($resourcesByIdentifier[$identifier])) {
            throw new \DomainException(
                'Duplicate identifiers are not allowed. Identifier "' . $identifier . '" has been deduced.',
                1562081584
            );
        }

        $resourcesByIdentifier[$identifier] = &$resource;

        return $identifier;
    }

    static protected function addResourceToIntraGraphRelationMap(array &$relationMap, string $resourceIdentifier, IntraGraphResourceRelation $intraGraphRelation, string $resourceSide, array $resource, string $key)
    {
        $identifier = '';

        switch ($resourceSide) {
            case static::RESOURCE_SIDE_AS_IS:
                $identifier = $intraGraphRelation->determineAsIsResourceIdentifier($resource, $key);
                break;
            case static::RESOURCE_SIDE_TO_BE:
                $identifier = $intraGraphRelation->determineToBeResourceIdentifier($resource, $key);
        }

        if (empty($identifier)) {
            return;
        }

        $relationMap[$identifier] = $resourceIdentifier;
    }

}
