<?php


namespace DFAU\Convergence\Schema;


interface PropertyQualifier
{

    public function propertyIsQualified($value, string $key) : bool;

}
