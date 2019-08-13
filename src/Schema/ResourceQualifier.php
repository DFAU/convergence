<?php


namespace DFAU\Convergence\Schema;


interface ResourceQualifier
{

    public function resourceIsQualified(array $resource, string $key) : bool;

}
