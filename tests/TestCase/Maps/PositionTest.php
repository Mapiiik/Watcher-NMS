<?php
declare(strict_types=1);

namespace App\Test\TestCase\Maps;

use App\Maps\Position;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Maps\Position Test Case
 */
#[UsesClass(Position::class)]
class PositionTest extends TestCase
{
    /**
     * A position keeps the two coordinates apart, in the order the map libraries name them.
     *
     * @return void
     * @link \App\Maps\Position::toArray()
     */
    public function testToArrayNamesBothCoordinates(): void
    {
        $position = new Position(50.0875, 14.4212);

        $this->assertSame(['lat' => 50.0875, 'lng' => 14.4212], $position->toArray());
    }
}
