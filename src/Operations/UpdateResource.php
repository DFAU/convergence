<?php declare(strict_types=1);


namespace DFAU\Convergence\Operations;


class UpdateResource extends AbstractResourceOperation
{
    /**
     * @var array
     */
    protected $resourceUpdates;

    public function __construct(array $resource, array $resourceUpdates)
    {
        $this->resource = $resource;
        $this->resourceUpdates = $resourceUpdates;
    }

    public function getResourceUpdates(): array
    {
        return $this->resourceUpdates;
    }

    public function jsonSerialize()
    {
        return array_merge(parent::jsonSerialize(), [
           'resourceUpdates' => $this->resourceUpdates
        ]);
    }
}
