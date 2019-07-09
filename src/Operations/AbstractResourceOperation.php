<?php declare(strict_types=1);


namespace DFAU\Convergence\Operations;


abstract class AbstractResourceOperation implements Operation
{
    /**
     * @var array
     */
    protected $resource;

    public function getResource() : array
    {
        return $this->resource;
    }

    protected function getType() {
        return (new \ReflectionClass($this))->getShortName();
    }

    public function jsonSerialize()
    {
        return [
            '@type' => $this->getType(),
            'resource' => $this->getResource()
        ];
    }

}
