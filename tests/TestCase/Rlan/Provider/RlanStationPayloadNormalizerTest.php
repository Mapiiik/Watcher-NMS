<?php
declare(strict_types=1);

namespace App\Test\TestCase\Rlan\Provider;

use App\Rlan\Provider\RlanStationPayloadNormalizer;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use RuntimeException;

/**
 * App\Rlan\Provider\RlanStationPayloadNormalizer Test Case
 *
 * The register is not ours and nothing it hands over is promised, so what these ask is what
 * happens when it hands over something else than last time.
 */
#[UsesClass(RlanStationPayloadNormalizer::class)]
class RlanStationPayloadNormalizerTest extends TestCase
{
    /**
     * A station as the register lists one.
     *
     * @return void
     * @link \App\Rlan\Provider\RlanStationPayloadNormalizer::stations()
     */
    public function testAStationIsRead(): void
    {
        $stations = RlanStationPayloadNormalizer::stations($this->listing([
            'id' => 8039,
            'lt' => 50.599546047948,
            'lg' => 15.511295692493,
            'ip' => '8040',
            'm' => '8039',
            'id_m' => 8039,
            't' => 'fs',
            'n' => '000005',
            'u' => 329,
            'pp' => 'a',
            'mac' => '04:D6:AA:A6:DF:74',
            's' => 'Aktivni',
            'tn' => 'FS PtP A #0008039',
        ]));

        $this->assertCount(1, $stations);

        $station = $stations[0];

        $this->assertSame(8039, $station->stationId);
        $this->assertSame(329, $station->userId);
        $this->assertSame(8040, $station->stationPairId);
        $this->assertSame('a', $station->pairPosition);
        $this->assertSame('fs', $station->type);
        $this->assertSame('000005', $station->name);
        $this->assertSame(50.599546047948, $station->latitude);
        $this->assertSame('Aktivni', $station->status);
        $this->assertNull($station->parametersRead);
    }

    /**
     * A station with nothing but a number is still a station, and the rest is simply not there.
     *
     * @return void
     * @link \App\Rlan\Provider\RlanStationPayloadNormalizer::stations()
     */
    public function testAStationWithNothingElseIsStillRead(): void
    {
        $stations = RlanStationPayloadNormalizer::stations($this->listing(['id' => 8039]));

        $this->assertCount(1, $stations);
        $this->assertSame(8039, $stations[0]->stationId);
        $this->assertNull($stations[0]->name);
        $this->assertNull($stations[0]->macAddress);
    }

    /**
     * An entry nothing identifies is passed over rather than taken for a station.
     *
     * @return void
     * @link \App\Rlan\Provider\RlanStationPayloadNormalizer::stations()
     */
    public function testAnEntryWithNoNumberIsPassedOver(): void
    {
        $this->assertSame([], RlanStationPayloadNormalizer::stations($this->listing(['n' => 'nameless'])));
    }

    /**
     * A field of a type nobody expected does not stop the reading.
     *
     * @return void
     * @link \App\Rlan\Provider\RlanStationPayloadNormalizer::stations()
     */
    public function testAFieldOfTheWrongTypeIsReadAsNotBeingThere(): void
    {
        $stations = RlanStationPayloadNormalizer::stations($this->listing([
            'id' => 8039,
            'n' => ['not', 'a', 'name'],
            'lt' => ['not' => 'a place'],
        ]));

        $this->assertCount(1, $stations);
        $this->assertNull($stations[0]->name);
        $this->assertNull($stations[0]->latitude);
    }

    /**
     * A kind of station nobody has heard of is still a station - the register answers with kinds
     * it does not write down anywhere.
     *
     * @return void
     * @link \App\Rlan\Provider\RlanStationPayloadNormalizer::stations()
     */
    public function testAnUnknownKindOfStationSurvives(): void
    {
        $stations = RlanStationPayloadNormalizer::stations($this->listing([
            'id' => 8039,
            't' => 'tg_58ghz',
        ]));

        $this->assertSame('tg_58ghz', $stations[0]->type);
    }

    /**
     * The address is written the way a MAC address is written, however it was typed in.
     *
     * @param string $typed What the registration holds.
     * @param string $expected What it is compared as.
     * @return void
     * @link \App\Rlan\Provider\RlanStationPayloadNormalizer::stations()
     */
    #[DataProvider('addressesProvider')]
    public function testTheAddressIsWrittenTheWayAnAddressIs(string $typed, string $expected): void
    {
        $stations = RlanStationPayloadNormalizer::stations($this->listing(['id' => 1, 'mac' => $typed]));

        $this->assertSame($expected, $stations[0]->macAddress);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function addressesProvider(): array
    {
        return [
            'already written that way' => ['04:d6:aa:a6:df:74', '04:d6:aa:a6:df:74'],
            'shouted' => ['04:D6:AA:A6:DF:74', '04:d6:aa:a6:df:74'],
            'with dashes' => ['04-D6-AA-A6-DF-74', '04:d6:aa:a6:df:74'],
            'run together' => ['04D6AAA6DF74', '04:d6:aa:a6:df:74'],
            // Not an address, and not thrown away either - it is still what the registration says.
            'not an address' => ['no idea', 'no idea'],
        ];
    }

    /**
     * The technical parameters, named the way this application names them.
     *
     * @return void
     * @link \App\Rlan\Provider\RlanStationPayloadNormalizer::parameters()
     */
    public function testTheParametersAreRead(): void
    {
        $parameters = RlanStationPayloadNormalizer::parameters($this->listing([
            'id' => '8039',
            'antenna_volume' => '42.00',
            'channel_width' => '2160.00',
            'power' => '10.00',
            'frequency' => '62640.00',
            'type_station' => [
                'direction' => '357',
                'eirp' => '52.00',
                'ratio_signal_interference' => '34',
            ],
        ]));

        $this->assertSame('42.00', $parameters[8039]['antenna_gain']);
        $this->assertSame('2160.00', $parameters[8039]['channel_width']);
        $this->assertSame(62640, $parameters[8039]['frequency']);
        $this->assertSame('357', $parameters[8039]['direction']);
        $this->assertSame('52.00', $parameters[8039]['eirp']);
    }

    /**
     * A station that picks its own channel is registered without one, and the register says so
     * with a zero rather than by leaving it out - which is not a channel of zero.
     *
     * @return void
     * @link \App\Rlan\Provider\RlanStationPayloadNormalizer::parameters()
     */
    public function testAFrequencyOfZeroIsNoFrequency(): void
    {
        $parameters = RlanStationPayloadNormalizer::parameters($this->listing([
            'id' => '38229',
            'frequency' => '0',
        ]));

        $this->assertNull($parameters[38229]['frequency']);
    }

    /**
     * A station with no per-kind block does not stop the reading of the rest.
     *
     * @return void
     * @link \App\Rlan\Provider\RlanStationPayloadNormalizer::parameters()
     */
    public function testAStationWithNoPerKindBlockIsRead(): void
    {
        $parameters = RlanStationPayloadNormalizer::parameters($this->listing([
            'id' => '8039',
            'power' => '10.00',
        ]));

        $this->assertSame('10.00', $parameters[8039]['power']);
        $this->assertNull($parameters[8039]['eirp']);
    }

    /**
     * A payload that is not one the register could have answered with is the one thing that
     * cannot be told from a reading that went wrong, so it is refused.
     *
     * @return void
     * @link \App\Rlan\Provider\RlanStationPayloadNormalizer::stations()
     */
    public function testAPayloadWithNoStationsInItIsRefused(): void
    {
        $this->expectException(RuntimeException::class);

        RlanStationPayloadNormalizer::stations(['status' => 500, 'error' => 'something went wrong']);
    }

    /**
     * A register holding nothing at all is not the same as a payload of the wrong shape.
     *
     * @return void
     * @link \App\Rlan\Provider\RlanStationPayloadNormalizer::stations()
     */
    public function testAnEmptyListingIsNotRefused(): void
    {
        $this->assertSame([], RlanStationPayloadNormalizer::stations(['status' => 200, 'data' => []]));
    }

    /**
     * One station wrapped the way the register wraps them.
     *
     * @param array<string, mixed> $station The station to wrap.
     * @return array<string, mixed>
     */
    private function listing(array $station): array
    {
        return [
            'status' => 200,
            // Keyed by the number of the station rather than a plain list, as the register answers.
            'data' => [(string)($station['id'] ?? 'x') => $station],
        ];
    }
}
