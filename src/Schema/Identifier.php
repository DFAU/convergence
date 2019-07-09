<?php declare(strict_types=1);


namespace DFAU\Convergence\Schema;


interface Identifier
{

    public function determineIdentity(array $resource, string $key) : string;
}
