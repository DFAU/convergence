<?php declare(strict_types=1);


namespace DFAU\Convergence\Schema;


final class InterGraphResourceRelation implements ResourceRelation
{

    /**
     * @var Identifier
     */
    protected $asIsResourceIdentifier;
    /**
     * @var Identifier
     */
    protected $toBeResourceIdentifier;

    public function __construct(Identifier $asIsResourceIdentifier, ?Identifier $toBeResourceIdentifier = null)
    {
        $this->asIsResourceIdentifier = $asIsResourceIdentifier;
        $this->toBeResourceIdentifier = $toBeResourceIdentifier ?? $asIsResourceIdentifier;
    }


    public function determineAsIsResourceIdentifier(array $resource, string $key): string
    {
        return $this->asIsResourceIdentifier->determineIdentity($resource, $key);
    }


    public function determineToBeResourceIdentifier(array $resource, string $key): string
    {
        return $this->toBeResourceIdentifier->determineIdentity($resource, $key);
    }
}
