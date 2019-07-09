<?php declare(strict_types=1);


namespace DFAU\Convergence\Schema;


class StringPropertyReferenceList implements ReferenceList
{

    const DELIMITER_COMMA = ',';

    /**
     * @var string
     */
    protected $listDelimiter;

    /**
     * @var string
     */
    protected $propertyName;

    public function __construct(string $propertyName, $listDelimiter = self::DELIMITER_COMMA)
    {
        $this->propertyName = $propertyName;
        $this->listDelimiter = $listDelimiter;
    }

    public function getReferencesFromResource(array $resource): array
    {
        $references = explode($this->listDelimiter, (string)$resource[$this->propertyName] ?: '');
        return array_map('trim', $references);
    }

    public function applyReferencesToResource(array $references, array $resource) : array
    {
        $referenceList = implode($this->listDelimiter, $references);
        $resource[$this->propertyName] = $referenceList;
        return $resource;
    }
}
