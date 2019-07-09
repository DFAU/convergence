<?php declare(strict_types=1);


namespace DFAU\Convergence;


use DFAU\Convergence\Schema\IntraGraphResourceRelation;

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

            [ $propertyNamesToAdd, $propertyNamesToRemove, $propertyNamesToUpdate ] = $this->compareLists(
                array_keys($toBeResource),
                array_keys($asIsResource)
            );

            $propertiesToAdd = array_combine($propertyNamesToAdd, array_map(function($propertyName) use ($toBeResource) {
                return $toBeResource[$propertyName];
            }, $propertyNamesToAdd));

            $propertiesToRemove = array_fill_keys($propertyNamesToRemove, null);

            $propertiesToUpdate = [];

            /** @var IntraGraphResourceRelation $intraGraphRelation */
            foreach ($this->relationMap as $intraGraphRelation) {
                if (!$intraGraphRelation->isSubjectQualified(array_merge($toBeResource, $asIsResource), $identifier)) {
                    continue;
                }

                $toBeRelations = $this->resolveRelationIdentifiers($toBeResourceMap, $intraGraphRelation, $toBeResource);
                $asIsRelations = $this->resolveRelationIdentifiers($this, $intraGraphRelation, $asIsResource);

                if (array_diff_assoc($toBeRelations, $asIsRelations) || array_diff_assoc($asIsRelations, $toBeRelations)) {
                    // TODO check whether it's right to provide the match identifiers here
                    $propertiesToUpdate = $intraGraphRelation->applyReferencesToResource($toBeRelations, $propertiesToUpdate);
                }
            }

            foreach ($propertyNamesToUpdate as $propertyName) {
                if (isset($propertiesToUpdate[$propertyName]) || $toBeResource[$propertyName] == $asIsResource[$propertyName]) {
                    continue;
                }
                $propertiesToUpdate[$propertyName] = $toBeResource[$propertyName];
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

    protected function resolveRelationIdentifiers(ResourceMap $resourceMap, IntraGraphResourceRelation $intraGraphRelation, $resource): array
    {
        return array_filter(array_map(function ($reference) use ($resourceMap, $intraGraphRelation) {
            return $resourceMap->relationMap[$intraGraphRelation][$reference] ?? null;
        }, $intraGraphRelation->getReferencesFromResource($resource)));
    }


    static public function fromSchemaAndResources(Schema $schema, string $resourceSide, array $resources): self
    {
        if (!in_array($resourceSide, [static::RESOURCE_SIDE_AS_IS, static::RESOURCE_SIDE_TO_BE])) {
            throw new \InvalidArgumentException(
                'Resource side must be either ::RESOURCE_SIDE_AS_IS or :: RESOURCE_SIDE_TO_BE. "' . $resourceSide . '" has been given instead.',
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
