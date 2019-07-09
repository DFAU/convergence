<?php declare(strict_types=1);


namespace DFAU\Convergence\Operations;


class AddResource extends AbstractResourceOperation
{
    public function __construct(array $resource)
    {
        $this->resource = $resource;
    }
}
