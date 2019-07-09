<?php


namespace DFAU\Convergence\Schema;


interface ResourceRelation
{

    public function determineAsIsResourceIdentifier(array $resource, string $key) : string;

    public function determineToBeResourceIdentifier(array $resource, string $key) : string;
}
