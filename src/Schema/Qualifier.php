<?php


namespace DFAU\Convergence\Schema;


interface Qualifier
{

    public function resourceIsQualified(array $resource, string $key) : bool;

}
