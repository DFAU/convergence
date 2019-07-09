<?php declare(strict_types=1);


namespace DFAU\Convergence\Tests;


use DFAU\Convergence\Schema;
use PHPUnit\Framework\TestCase;

class SchemaTest extends TestCase
{

    public function testValidatesInterGraphRelationsUponConstruction()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1562062730);

        new Schema([new \stdClass()]);
    }

    public function testValidatesIntraGraphRelationsUponConstruction()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1562149887);

        new Schema(
            [new Schema\InterGraphResourceRelation(new Schema\ExpressionIdentifier('resource.uid'))],
            [new \stdClass()]
        );
    }
}
