<?php declare(strict_types=1);


namespace DFAU\Convergence;


use DFAU\Convergence\Operations\AddResource;
use DFAU\Convergence\Operations\Operation;
use DFAU\Convergence\Operations\RemoveResource;
use DFAU\Convergence\Operations\UpdateResource;
use DFAU\Convergence\Schema\InterGraphResourceRelation;

class OperationsFactory
{

    /**
     * @param Schema $schema
     * @param array[] $toBeResources
     * @param array[] $asIsResources
     * @return Operation[]
     */
    public function buildFromSchemaAndResources(Schema $schema, array $toBeResources, array $asIsResources): array
    {
        $toBeResourceMap = ResourceMap::fromSchemaAndResources($schema, ResourceMap::RESOURCE_SIDE_TO_BE, $toBeResources);
        $asIsResourceMap = ResourceMap::fromSchemaAndResources($schema, ResourceMap::RESOURCE_SIDE_AS_IS, $asIsResources);

        $comparisonResults = $asIsResourceMap->compare($toBeResourceMap);

        $operations = array_map(fn($resource) => new AddResource($resource), $comparisonResults[ResourceMap::COMPARISON_RESOURCES_TO_ADD]);

        $operations = array_merge($operations, array_map(fn($resource) => new RemoveResource($resource), $comparisonResults[ResourceMap::COMPARISON_RESOURCES_TO_REMOVE]));

        foreach ($comparisonResults[ResourceMap::COMPARISON_RESOURCES_TO_UPDATE] as $resourceUpdateComparisonResult) {
            $resourceUpdates = [];
            if (!empty($resourceUpdateComparisonResult[ResourceMap::COMPARISON_PROPERTIES_TO_ADD])) {
                $resourceUpdates = array_merge($resourceUpdates, $resourceUpdateComparisonResult[ResourceMap::COMPARISON_PROPERTIES_TO_ADD]);
            }
            if (!empty($resourceUpdateComparisonResult[ResourceMap::COMPARISON_PROPERTIES_TO_REMOVE])) {
                $resourceUpdates = array_merge($resourceUpdates, $resourceUpdateComparisonResult[ResourceMap::COMPARISON_PROPERTIES_TO_REMOVE]);
            }
            if (!empty($resourceUpdateComparisonResult[ResourceMap::COMPARISON_PROPERTIES_TO_UPDATE])) {
                $resourceUpdates = array_merge($resourceUpdates, $resourceUpdateComparisonResult[ResourceMap::COMPARISON_PROPERTIES_TO_UPDATE]);
            }
            if ($resourceUpdates !== []) {
                $operations[] = new UpdateResource(
                    $resourceUpdateComparisonResult[ResourceMap::RESOURCE_SIDE_AS_IS],
                    $resourceUpdates
                );
            }
        }

        return $operations;
    }
}
