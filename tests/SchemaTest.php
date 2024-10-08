<?php declare(strict_types=1);


namespace DFAU\Convergence\Tests;


use DFAU\Convergence\Schema\InterGraphResourceRelation;
use DFAU\Convergence\Schema\ExpressionIdentifier;
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
            [new InterGraphResourceRelation(new ExpressionIdentifier('resource.uid'))],
            [new \stdClass()]
        );
    }
}
