<?php


namespace DFAU\Convergence\Schema;


interface PropertyList
{

    public function getPropertiesFromResource(array $resource): array;

    public function applyPropertiesToResource(array $properties, array $resource): array;

}
