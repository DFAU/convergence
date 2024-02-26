<?php


namespace DFAU\Convergence\Schema;


interface ReferenceList
{
    public const DEFAULT_REFERENCE_PREDICATE = '__DEFAULT__';

    public function getAvailablePredicates(array $resource): array;

    public function getReferencesFromResource(array $resource, string $predicate = self::DEFAULT_REFERENCE_PREDICATE): array;

    public function applyReferencesToResource(array $relationResources, array $references, array $resource, string $predicate = self::DEFAULT_REFERENCE_PREDICATE) : array;
}
