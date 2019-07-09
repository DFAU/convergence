<?php declare(strict_types=1);


namespace DFAU\Convergence;


use DFAU\Convergence\Schema\InterGraphResourceRelation;
use DFAU\Convergence\Schema\IntraGraphResourceRelation;

class Schema
{

    protected const PROPERTY_SEPARATOR = "\0";

    /**
     * @var \SplObjectStorage<InterGraphResourceRelation>
     */
    protected $interGraphRelations;

    /**
     * @var \SplObjectStorage<IntraGraphResourceRelation>
     */
    protected $intraGraphReferenceResourceRelations;

    public function __construct(array $interGraphRelations, array $intraGraphReferenceResourceRelations = [])
    {
        $this->interGraphRelations = new \SplObjectStorage();
        foreach ($interGraphRelations as $interGraphRelation) {
            if (!$interGraphRelation instanceof InterGraphResourceRelation) {
                throw new \InvalidArgumentException(
                    'Argument $interGraphRelations does not contain only objects of type ' . InterGraphResourceRelation::class . '. ' . gettype($interGraphRelation) . ' is given instead.',
                    1562062730
                );
            }
            $this->interGraphRelations->attach($interGraphRelation);
        }

        $this->intraGraphReferenceResourceRelations = new \SplObjectStorage();
        foreach($intraGraphReferenceResourceRelations as $intraGraphReferenceResourceRelation) {
            if (!$intraGraphReferenceResourceRelation instanceof IntraGraphResourceRelation) {
                throw new \InvalidArgumentException(
                    'Argument $intraGraphReferenceResourceRelations does not contain only objects of type ' . IntraGraphResourceRelation::class . '. ' . gettype($intraGraphReferenceResourceRelation) . ' is given instead.',
                    1562149887
                );
            }
            $this->intraGraphReferenceResourceRelations->attach($intraGraphReferenceResourceRelation);
        }
    }

    /**
     * @return \SplObjectStorage<InterGraphResourceRelation>
     */
    public function getInterGraphRelations(): \SplObjectStorage
    {
        return $this->interGraphRelations;
    }

    /**
     * @return \SplObjectStorage<IntraGraphResourceRelation>
     */
    public function getIntraGraphRelations(): \SplObjectStorage
    {
        return $this->intraGraphReferenceResourceRelations;
    }
}
