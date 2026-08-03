<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Behavior;

use App\Model\Behavior\StringModificationsBehavior;
use App\Model\Table\AntennaTypesTable;
use Cake\TestSuite\TestCase;
use Override;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Model\Behavior\StringModificationsBehavior Test Case
 *
 * The behavior tidies up incoming strings before they are marshalled, so it is exercised through a
 * table that carries it rather than by calling the callback directly - going through a table is the
 * only way it ever runs in the application.
 */
#[UsesClass(StringModificationsBehavior::class)]
class StringModificationsBehaviorTest extends TestCase
{
    /**
     * Table carrying the behavior
     *
     * @var \App\Model\Table\AntennaTypesTable
     */
    protected AntennaTypesTable $AntennaTypes;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.AppUsers',
        'app.RadioUnitBands',
        'app.Manufacturers',
        'app.AntennaTypes',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        /** @var \App\Model\Table\AntennaTypesTable $antennaTypes */
        $antennaTypes = $this->getTableLocator()->get('AntennaTypes');
        $this->AntennaTypes = $antennaTypes;
    }

    /**
     * Surrounding whitespace is dropped, so a pasted value does not end up merely looking like the
     * one already stored.
     *
     * @return void
     * @link \App\Model\Behavior\StringModificationsBehavior::beforeMarshal()
     */
    public function testBeforeMarshalTrimsStrings(): void
    {
        $antennaType = $this->AntennaTypes->newEntity(['name' => "  Lorem ipsum \n"]);

        $this->assertSame('Lorem ipsum', $antennaType->name);
    }

    /**
     * A field left blank means it is not filled in, not that it holds an empty string - otherwise
     * two ways of saying the same thing both end up in the column.
     *
     * @return void
     * @link \App\Model\Behavior\StringModificationsBehavior::beforeMarshal()
     */
    public function testBeforeMarshalTurnsBlanksIntoNull(): void
    {
        $antennaType = $this->AntennaTypes->newEntity(['name' => '', 'part_number' => '   ']);

        $this->assertNull($antennaType->name);
        $this->assertNull($antennaType->part_number);
    }

    /**
     * The en dash is what a word processor makes of a typed hyphen, so it arrives whenever a value
     * was written there first. It is replaced, or the same name stops matching itself.
     *
     * @return void
     * @link \App\Model\Behavior\StringModificationsBehavior::beforeMarshal()
     */
    public function testBeforeMarshalReplacesTheEnDash(): void
    {
        $antennaType = $this->AntennaTypes->newEntity(['name' => 'Lorem – ipsum']);

        $this->assertSame('Lorem - ipsum', $antennaType->name);
    }

    /**
     * Only strings are touched; anything else is passed on as it came.
     *
     * @return void
     * @link \App\Model\Behavior\StringModificationsBehavior::beforeMarshal()
     */
    public function testBeforeMarshalLeavesOtherTypesAlone(): void
    {
        $antennaType = $this->AntennaTypes->newEntity(['name' => 'Lorem', 'antenna_gain' => 7]);

        $this->assertSame(7, $antennaType->antenna_gain);
    }

    /**
     * The tidying happens on the way in rather than on the way to the database, so what is read
     * back is what a later comparison sees.
     *
     * @return void
     * @link \App\Model\Behavior\StringModificationsBehavior::beforeMarshal()
     */
    public function testBeforeMarshalAppliesToSavedRecords(): void
    {
        $antennaType = $this->AntennaTypes->newEntity(['name' => '  Lorem – ipsum  ']);
        $this->AntennaTypes->saveOrFail($antennaType);

        $this->assertSame('Lorem - ipsum', $this->AntennaTypes->get($antennaType->id)->name);
    }
}
