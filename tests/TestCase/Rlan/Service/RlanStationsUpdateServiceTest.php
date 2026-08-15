<?php
declare(strict_types=1);

namespace App\Test\TestCase\Rlan\Service;

use App\Rlan\Provider\RlanStationProviderPayload;
use App\Rlan\Service\RlanStationsUpdateService;
use Cake\ORM\Table;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use RuntimeException;

/**
 * App\Rlan\Service\RlanStationsUpdateService Test Case
 *
 * The whole mirror is rewritten by every reading, so what these ask is what happens to a station
 * that was there before - and what happens to the mirror when the reading says nothing.
 */
#[UsesClass(RlanStationsUpdateService::class)]
class RlanStationsUpdateServiceTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.AppUsers',
        'app.RlanStations',
    ];

    /**
     * A station the register names is written down.
     *
     * @return void
     * @link \App\Rlan\Service\RlanStationsUpdateService::updateNow()
     */
    public function testAStationIsWrittenDown(): void
    {
        $read = (new RlanStationsUpdateService(new RlanStationProviderPayload(
            $this->listing([['id' => 9100, 'n' => 'Newly filed', 'mac' => '11:22:33:44:55:66']]),
        )))->updateNow();

        $this->assertSame(1, $read);

        $station = $this->stations()->find()->where(['station_id' => 9100])->firstOrFail();

        $this->assertSame('Newly filed', $station->get('name'));
        $this->assertSame('11:22:33:44:55:66', $station->get('mac_address'));
    }

    /**
     * Reading twice does not write the same station down twice - it is recognised by the number
     * the register keeps it under.
     *
     * @return void
     * @link \App\Rlan\Service\RlanStationsUpdateService::updateNow()
     */
    public function testReadingTwiceDoesNotDuplicateAStation(): void
    {
        $listing = $this->listing([['id' => 9100, 'n' => 'Filed once']]);

        (new RlanStationsUpdateService(new RlanStationProviderPayload($listing)))->updateNow();
        (new RlanStationsUpdateService(new RlanStationProviderPayload($listing)))->updateNow();

        $this->assertSame(1, $this->stations()->find()->where(['station_id' => 9100])->count());
    }

    /**
     * A station the register has stopped naming is a station that goes.
     *
     * @return void
     * @link \App\Rlan\Service\RlanStationsUpdateService::updateNow()
     */
    public function testAStationTheRegisterNoLongerNamesIsRemoved(): void
    {
        // 8039 is the station the fixture holds, and this reading does not name it.
        (new RlanStationsUpdateService(new RlanStationProviderPayload(
            $this->listing([['id' => 9100, 'n' => 'The only one left']]),
        )))->updateNow();

        $this->assertSame(0, $this->stations()->find()->where(['station_id' => 8039])->count());
        $this->assertSame(1, $this->stations()->find()->count());
    }

    /**
     * What the register says about a station it still names replaces what was written down before.
     *
     * @return void
     * @link \App\Rlan\Service\RlanStationsUpdateService::updateNow()
     */
    public function testAStationTheRegisterStillNamesIsBroughtUpToDate(): void
    {
        (new RlanStationsUpdateService(new RlanStationProviderPayload(
            $this->listing([['id' => 8039, 'n' => 'Filed under something else now']]),
        )))->updateNow();

        $station = $this->stations()->find()->where(['station_id' => 8039])->firstOrFail();

        $this->assertSame('Filed under something else now', $station->get('name'));
    }

    /**
     * The technical parameters of a reading are put with the stations they belong to, and a
     * station that was asked about and had none says so.
     *
     * @return void
     * @link \App\Rlan\Service\RlanStationsUpdateService::updateNow()
     */
    public function testTheParametersAreWrittenWithTheStations(): void
    {
        (new RlanStationsUpdateService(new RlanStationProviderPayload(
            $this->listing([['id' => 9100], ['id' => 9101]]),
            [$this->listing([['id' => 9100, 'channel_width' => '2160.00', 'power' => '10.00']])],
        )))->updateNow();

        $withParameters = $this->stations()->find()->where(['station_id' => 9100])->firstOrFail();
        $withoutParameters = $this->stations()->find()->where(['station_id' => 9101])->firstOrFail();

        $this->assertSame('2160.000', $withParameters->get('channel_width'));
        $this->assertNotNull($withParameters->get('parameters_read'));

        // Asked about and there were none, which is a different thing from never having asked.
        $this->assertNull($withoutParameters->get('channel_width'));
        $this->assertNotNull($withoutParameters->get('parameters_read'));
    }

    /**
     * A reading that names nothing empties the mirror, which is the one thing this must never do
     * by accident - so it refuses instead.
     *
     * @return void
     * @link \App\Rlan\Service\RlanStationsUpdateService::updateNow()
     */
    public function testAReadingThatNamesNothingChangesNothing(): void
    {
        try {
            (new RlanStationsUpdateService(new RlanStationProviderPayload($this->listing([]))))->updateNow();
            $this->fail('A reading naming no stations should have been refused.');
        } catch (RuntimeException) {
            $this->assertSame(1, $this->stations()->find()->count());
        }
    }

    /**
     * The stations of the register, wrapped the way the register wraps them.
     *
     * @param list<array<string, mixed>> $stations The stations to wrap.
     * @return array<string, mixed>
     */
    private function listing(array $stations): array
    {
        $data = [];
        foreach ($stations as $station) {
            $data[(string)$station['id']] = $station;
        }

        return ['status' => 200, 'data' => $data];
    }

    /**
     * @return \Cake\ORM\Table
     */
    private function stations(): Table
    {
        return $this->getTableLocator()->get('RlanStations');
    }
}
