<?php declare(strict_types=1);


namespace DFAU\Convergence\Test;


use DFAU\Convergence\OperationsFactory;
use DFAU\Convergence\Schema;
use DFAU\Convergence\Schema\ExpressionIdentifier;
use DFAU\Convergence\Schema\InterGraphResourceRelation;
use PHPUnit\Framework\TestCase;

class OperationsFactoryTest extends TestCase
{

    /**
     * @dataProvider provideData
     */
    public function testOperationsFactory(array $targetResources, array $currentResources, string $expectedOperationsResult)
    {
        $schema = $this->createSchema();

        $operationsFactory = new OperationsFactory();
        $operationsResult = $operationsFactory->buildFromSchemaAndResources($schema, $targetResources, $currentResources);
        $this->assertJsonStringEqualsJsonString(
            $expectedOperationsResult,
            json_encode($operationsResult)
        );


    }

    public function provideData()
    {
        return [
            'resourceAddition' => [
                [['_type' => 'pages', 'uid' => 1, 'title' => 'Test To Be']],
                [],
                '[{"@type":"AddResource","resource":{"_type":"pages","uid":1,"title":"Test To Be"}}]'
            ],
            'resourceRemoval' => [
                [],
                [['_type' => 'pages', 'uid' => 1, 'title' => 'Test As Is']],
                '[{"@type":"RemoveResource","resource":{"_type":"pages","uid":1,"title":"Test As Is"}}]'
            ],
            'propertiesUpdate' => [
                [['_type' => 'pages', 'uid' => 1, 'title' => 'Test To Be']],
                [['_type' => 'pages', 'uid' => 1, 'title' => 'Test As Is']],
                '[{"@type":"UpdateResource","resource":{"_type":"pages","uid":1,"title":"Test As Is"},"resourceUpdates":{"title":"Test To Be"}}]'
            ],
            // TODO refine those following datasets and operations
            'propertyAddition' => [
                [['_type' => 'pages', 'uid' => 1, 'title' => 'Test As Is', 'bodytext' => 'Foo Bar']],
                [['_type' => 'pages', 'uid' => 1, 'title' => 'Test As Is']],
                '[{"@type":"UpdateResource","resource":{"_type":"pages","uid":1,"title":"Test As Is"},"resourceUpdates":{"bodytext":"Foo Bar"}}]'
            ],
            'propertyRemoval' => [
                [['_type' => 'pages', 'uid' => 1, 'title' => 'Test As Is']],
                [['_type' => 'pages', 'uid' => 1, 'title' => 'Test As Is', 'bodytext' => 'Foo Bar']],
                '[{"@type":"UpdateResource","resource":{"_type":"pages","uid":1,"title":"Test As Is","bodytext":"Foo Bar"},"resourceUpdates":{"bodytext":null}}]'
            ],
            'relationAddition' => [
                [['_type' => 'pages', 'uid' => 1, 'title' => 'Test To Be'], ['_type' => 'tt_content', 'pid' => 1, 'uid' => 2, 'title' => 'Test Content As Is']],
                [ ['_type' => 'tt_content', 'pid' => 1, 'uid' => 2, 'title' => 'Test Content As Is']],
                '[{"@type":"AddResource","resource":{"_type":"pages","uid":1,"title":"Test To Be"}},{"@type":"UpdateResource","resource":{"_type":"tt_content","pid":1,"uid":2,"title":"Test Content As Is"},"resourceUpdates":{"pid":"pages_1"}}]'
            ],
            'relationRemoval' => [
                [['_type' => 'tt_content', 'pid' => 1, 'uid' => 2, 'title' => 'Test Content As Is']],
                [['_type' => 'pages', 'uid' => 1, 'title' => 'Test As Is'], ['_type' => 'tt_content', 'pid' => 1, 'uid' => 2, 'title' => 'Test Content As Is']],
                '[{"@type":"RemoveResource","resource":{"_type":"pages","uid":1,"title":"Test As Is"}},{"@type":"UpdateResource","resource":{"_type":"tt_content","pid":1,"uid":2,"title":"Test Content As Is"},"resourceUpdates":{"pid":""}}]'
            ]
        ];
    }


    protected function createSchema()
    {
        return new Schema(
            [new InterGraphResourceRelation(new ExpressionIdentifier('resource._type~"_"~resource.uid'))],
            [new Schema\IntraGraphResourceRelation(
                new Schema\ExpressionQualifier('resource._type == "tt_content"'),
                new Schema\StringPropertyReferenceList('pid'),
                new ExpressionIdentifier('resource._type == "pages" ? resource.uid : ""')
            )]
        );
    }
}
