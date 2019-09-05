<?php


namespace DFAU\Convergence\Schema;


interface OrderedRelationQualifier
{

    public function resourceRelationIsOrdered(array $resource, string $predicate) : bool;

}
