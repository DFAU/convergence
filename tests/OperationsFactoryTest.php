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
    public function testOperationsFactory(Schema $schema, array $targetResources, array $currentResources, string $expectedOperationsResult)
    {
        $operationsFactory = new OperationsFactory();
        $operationsResult = $operationsFactory->buildFromSchemaAndResources($schema, $targetResources, $currentResources);
        $this->assertJsonStringEqualsJsonString(
            $expectedOperationsResult,
            json_encode($operationsResult)
        );
    }

    public function provideData()
    {
        $schema = $this->createSchema();
        $jsonApiSchema = $this->createJsonApiSchema();

        // TODO refine those following datasets and operations
        return [
            'resourceAddition' => [
                $schema,
                [['_type' => 'pages', 'uid' => 1, 'title' => 'Test To Be']],
                [],
                '[{"@type":"AddResource","resource":{"_type":"pages","uid":1,"title":"Test To Be"}}]'
            ],
            'resourceRemoval' => [
                $schema,
                [],
                [['_type' => 'pages', 'uid' => 1, 'title' => 'Test As Is']],
                '[{"@type":"RemoveResource","resource":{"_type":"pages","uid":1,"title":"Test As Is"}}]'
            ],
            'propertiesUpdate' => [
                $schema,
                [['_type' => 'pages', 'uid' => 1, 'title' => 'Test To Be']],
                [['_type' => 'pages', 'uid' => 1, 'title' => 'Test As Is']],
                '[{"@type":"UpdateResource","resource":{"_type":"pages","uid":1,"title":"Test As Is"},"resourceUpdates":{"title":"Test To Be"}}]'
            ],
            'propertyAddition' => [
                $schema,
                [['_type' => 'pages', 'uid' => 1, 'title' => 'Test As Is', 'bodytext' => 'Foo Bar']],
                [['_type' => 'pages', 'uid' => 1, 'title' => 'Test As Is']],
                '[{"@type":"UpdateResource","resource":{"_type":"pages","uid":1,"title":"Test As Is"},"resourceUpdates":{"bodytext":"Foo Bar"}}]'
            ],
            'propertyRemoval' => [
                $schema,
                [['_type' => 'pages', 'uid' => 1, 'title' => 'Test As Is']],
                [['_type' => 'pages', 'uid' => 1, 'title' => 'Test As Is', 'bodytext' => 'Foo Bar']],
                '[{"@type":"UpdateResource","resource":{"_type":"pages","uid":1,"title":"Test As Is","bodytext":"Foo Bar"},"resourceUpdates":{"bodytext":null}}]'
            ],
            'relationAddition' => [
                $schema,
                [['_type' => 'pages', 'uid' => 1, 'title' => 'Test To Be'], ['_type' => 'tt_content', 'pid' => 1, 'uid' => 2, 'title' => 'Test Content As Is']],
                [ ['_type' => 'tt_content', 'pid' => 1, 'uid' => 2, 'title' => 'Test Content As Is']],
                '[{"@type":"AddResource","resource":{"_type":"pages","uid":1,"title":"Test To Be"}},{"@type":"UpdateResource","resource":{"_type":"tt_content","pid":1,"uid":2,"title":"Test Content As Is"},"resourceUpdates":{"pid":"pages_1"}}]'
            ],
            'relationRemoval' => [
                $schema,
                [['_type' => 'tt_content', 'pid' => 1, 'uid' => 2, 'title' => 'Test Content As Is']],
                [['_type' => 'pages', 'uid' => 1, 'title' => 'Test As Is'], ['_type' => 'tt_content', 'pid' => 1, 'uid' => 2, 'title' => 'Test Content As Is']],
                '[{"@type":"RemoveResource","resource":{"_type":"pages","uid":1,"title":"Test As Is"}},{"@type":"UpdateResource","resource":{"_type":"tt_content","pid":1,"uid":2,"title":"Test Content As Is"},"resourceUpdates":{"pid":""}}]'
            ],
            'jsonApiResourceAddition' => [
                $jsonApiSchema,
                [['type' => 'pages', 'id' => 1, 'attributes' => ['title' => 'Test To Be']]],
                [],
                '[{"@type":"AddResource","resource":{"type":"pages","id":1,"attributes":{"title":"Test To Be"}}}]'
            ],
            'jsonApiResourceRemoval' => [
                $jsonApiSchema,
                [],
                [['type' => 'pages', 'id' => 1, 'attributes' => ['title' => 'Test As Is']]],
                '[{"@type":"RemoveResource","resource":{"type":"pages","id":1,"attributes":{"title":"Test As Is"}}}]'
            ],
            'jsonApiPropertiesUpdate' => [
                $jsonApiSchema,
                [['type' => 'pages', 'id' => 1, 'attributes' => ['title' => 'Test To Be', 'unchanged' => true]]],
                [['type' => 'pages', 'id' => 1, 'attributes' => ['title' => 'Test As Is', 'unchanged' => true]]],
                '[{"@type":"UpdateResource","resource":{"type":"pages","id":1,"attributes":{"title":"Test As Is","unchanged":true}},"resourceUpdates":{"attributes":{"title":"Test To Be"}}}]'
            ],
            'jsonApiPropertyAddition' => [
                $jsonApiSchema,
                [['type' => 'pages', 'id' => 1, 'attributes' => ['title' => 'Test As Is', 'bodytext' => 'Foo Bar']]],
                [['type' => 'pages', 'id' => 1, 'attributes' => ['title' => 'Test As Is']]],
                '[{"@type":"UpdateResource","resource":{"type":"pages","id":1,"attributes":{"title":"Test As Is"}},"resourceUpdates":{"attributes":{"bodytext":"Foo Bar"}}}]'
            ],
            'jsonApiPropertyRemoval' => [
                $jsonApiSchema,
                [['type' => 'pages', 'id' => 1, 'attributes' => ['title' => 'Test As Is']]],
                [['type' => 'pages', 'id' => 1, 'attributes' => ['title' => 'Test As Is', 'bodytext' => 'Foo Bar']]],
                '[{"@type":"UpdateResource","resource":{"type":"pages","id":1,"attributes":{"title":"Test As Is","bodytext":"Foo Bar"}},"resourceUpdates":{"attributes":{"bodytext":null}}}]'
            ],
            'jsonApiRelationAddition' => [
                $jsonApiSchema,
                [['type' => 'pages', 'id' => 1, 'attributes'  => ['title' => 'Test To Be']], ['type' => 'tt_content', 'id' => 2, 'attributes' => ['pid' => 1, 'title' => 'Test Content As Is']]],
                [['type' => 'tt_content', 'id' => 2, 'attributes' => ['pid' => 1, 'title' => 'Test Content As Is']]],
                '[{"@type":"AddResource","resource":{"type":"pages","id":1,"attributes":{"title":"Test To Be"}}},{"@type":"UpdateResource","resource":{"type":"tt_content","id":2,"attributes":{"pid":1,"title":"Test Content As Is"}},"resourceUpdates":{"attributes":{"pid":"pages_1"}}}]'
            ],
            'jsonApiRelationRemoval' => [
                $jsonApiSchema,
                [['type' => 'tt_content', 'id' => 2, 'attributes' => ['pid' => 1, 'title' => 'Test Content As Is']]],
                [['type' => 'pages', 'id' => 1, 'attributes'  => ['title' => 'Test As Is']], ['type' => 'tt_content', 'id' => 2, 'attributes' => ['pid' => 1, 'title' => 'Test Content As Is']]],
                '[{"@type":"RemoveResource","resource":{"type":"pages","id":1,"attributes":{"title":"Test As Is"}}},{"@type":"UpdateResource","resource":{"type":"tt_content","id":2,"attributes":{"pid":1,"title":"Test Content As Is"}},"resourceUpdates":{"attributes":{"pid":""}}}]'
            ],
        ];
    }


    protected function createSchema()
    {
        return new Schema(
            [new InterGraphResourceRelation(new ExpressionIdentifier('resource["_type"]~"_"~resource["uid"]'))],
            [new Schema\IntraGraphResourceRelation(
                new Schema\ExpressionQualifier('resource["_type"] == "tt_content"'),
                new Schema\StringPropertyPathReferenceList('resource[pid]'),
                new ExpressionIdentifier('resource["_type"] == "pages" ? resource["uid"] : ""')
            )],
            [new Schema\ResourcePropertiesExtractor(
                new Schema\ExpressionQualifier('resource["_type"]'),
                new Schema\PropertyPathPropertyList('resource')
            )]
        );
    }


    protected function createJsonApiSchema()
    {
        return new Schema(
            [new Schema\InterGraphResourceRelation(new Schema\ExpressionIdentifier('resource["type"]~"_"~resource["id"]'))],
            [
                new Schema\IntraGraphResourceRelation(
                    new Schema\ExpressionQualifier('resource["type"] != "pages"'),
                    new Schema\StringPropertyPathReferenceList('resource[attributes][pid]'),
                    new Schema\ExpressionIdentifier('resource["type"] == "pages" ? resource["id"] : ""')
                )
            ],
            [new Schema\ResourcePropertiesExtractor(
                new Schema\ExpressionQualifier('resource["type"] && resource["id"]'),
                new Schema\PropertyPathPropertyList('resource[attributes]')
            )]
        );
    }
}
