<?php


namespace DFAU\Convergence\Schema;


interface ReferenceList
{

    public function getReferencesFromResource(array $resource): array;

    public function applyReferencesToResource(array $references, array $resource) : array;
}
