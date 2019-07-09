<?php declare(strict_types=1);


namespace DFAU\Convergence\Tests;


use DFAU\Convergence\ResourceMap;
use DFAU\Convergence\Schema;
use DFAU\Convergence\Schema\ExpressionIdentifier;
use DFAU\Convergence\Schema\InterGraphResourceRelation;
use PHPUnit\Framework\TestCase;

class ResourceMapTest extends TestCase
{

    public function testResourceSideArgumentValidation()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1562588992);

        ResourceMap::fromSchemaAndResources($this->createSchema(), 'foo', []);
    }

    public function testEmptyIdentifierFromBrokenSchema()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1562576855);

        ResourceMap::fromSchemaAndResources(
            $this->createRogueSchema(),
            ResourceMap::RESOURCE_SIDE_AS_IS,
            [['_type' => 'pages', 'uid' => 1]]
        );
    }

    public function testEmptyIdentifierFromBrokenResource()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1562576855);

        ResourceMap::fromSchemaAndResources(
            $this->createSchema(),
            ResourceMap::RESOURCE_SIDE_AS_IS,
            [['_type' => 'pages', 'uid' => '']]
        );
    }

    public function testDuplicateIdentifier()
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionCode(1562081584);

        ResourceMap::fromSchemaAndResources(
            $this->createSchema(),
            ResourceMap::RESOURCE_SIDE_AS_IS,
            [['_type' => 'pages', 'uid' => 1], ['_type' => 'pages', 'uid' => 1]]
        );
    }

    public function testComparsionWithDistinctSchema()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1562592226);

        $toBe = ResourceMap::fromSchemaAndResources(
            $this->createSchema(),
            ResourceMap::RESOURCE_SIDE_TO_BE,
            [['_type' => 'pages', 'uid' => 1]]
        );
        $asIs = ResourceMap::fromSchemaAndResources(
            $this->createSchema(),
            ResourceMap::RESOURCE_SIDE_AS_IS,
            [['_type' => 'pages', 'uid' => 1]]
        );

        $asIs->compare($toBe);
    }

    protected function createSchema()
    {
        return new Schema(
            [new InterGraphResourceRelation(new ExpressionIdentifier('resource.uid'))]
        );
    }

    protected function createRogueSchema()
    {
        return new Schema(
            [new InterGraphResourceRelation(new ExpressionIdentifier('""'))]
        );
    }
}
