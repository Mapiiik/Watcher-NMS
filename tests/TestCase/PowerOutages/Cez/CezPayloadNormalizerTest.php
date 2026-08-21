<?php
declare(strict_types=1);

namespace App\Test\TestCase\PowerOutages\Cez;

use App\PowerOutages\Cez\CezPayloadNormalizer;
use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use Override;
use RuntimeException;

/**
 * App\PowerOutages\Cez\CezPayloadNormalizer Test Case
 *
 * The bodies below are what the distributor actually answered, kept verbatim rather than written
 * to suit the reader. They are the only description of these two shapes there is - neither of them
 * is documented anywhere - so a payload that stops being read the way this expects is the first
 * thing to look at when the mirror goes wrong.
 */
class CezPayloadNormalizerTest extends TestCase
{
    /**
     * setUp method
     *
     * @return void
     */
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        Configure::write('PowerOutages.bezstavyCdnUrl', 'https://cdn.example.com');
    }

    /**
     * The municipality with nothing planned is the ordinary answer, not a broken one.
     *
     * Worth its own test because it is the shape most municipalities answer with most nights: the
     * list of outages is absent rather than empty, and reading that as a failure would have the
     * mirror throwing on almost every reading.
     *
     * @return void
     * @link \App\PowerOutages\Cez\CezPayloadNormalizer::fromTown()
     */
    public function testNothingPlannedIsReadAsNoOutages(): void
    {
        $this->assertSame([], CezPayloadNormalizer::fromTown(['outages' => null], 533190));
    }

    /**
     * An answer that has the list but wrote it as something else is refused.
     *
     * @return void
     * @link \App\PowerOutages\Cez\CezPayloadNormalizer::fromTown()
     */
    public function testAListThatIsNotAListIsRefused(): void
    {
        $this->expectException(RuntimeException::class);

        CezPayloadNormalizer::fromTown(['outages' => null, 'outages_in_town' => 'nonsense'], 533165);
    }

    /**
     * The same, on the other of the two shapes.
     *
     * @return void
     * @link \App\PowerOutages\Cez\CezPayloadNormalizer::fromEan()
     */
    public function testAnAnswerAboutASupplyPointThatIsNotAListIsRefused(): void
    {
        $this->expectException(RuntimeException::class);

        CezPayloadNormalizer::fromEan(['data' => 'nonsense'], '859182400000001231');
    }

    /**
     * An outage read by municipality, down to the parcels and the house numbers.
     *
     * @return void
     * @link \App\PowerOutages\Cez\CezPayloadNormalizer::fromTown()
     */
    public function testAnOutageReadByMunicipality(): void
    {
        $outages = CezPayloadNormalizer::fromTown($this->townPayload(), 533165);

        $this->assertCount(1, $outages);
        $outage = $outages[0];

        $this->assertSame('110061112294', $outage->outageNumber);
        $this->assertFalse($outage->cancelled);

        // Written as coordinated universal time, and read as such.
        $this->assertSame('2026-09-03 06:00:00', $outage->beginsAt?->setTimezone('UTC')->format('Y-m-d H:i:s'));
        $this->assertSame('2026-09-03 11:00:00', $outage->endsAt?->setTimezone('UTC')->format('Y-m-d H:i:s'));

        $this->assertSame(533165, $outage->townCode);
        $this->assertSame('Kolin', $outage->townName);
        $this->assertSame('Kolin', $outage->district);
        $this->assertSame('Kolin VI, Hlubocska', $outage->summary);

        $this->assertSame(
            [['cadastral_code' => '668150', 'plot' => '5152/6']],
            $outage->places['parcels'],
        );

        $street = $outage->places['streets'][0];
        $this->assertSame(533165, $street['town_code']);
        $this->assertSame('Kolin VI', $street['town_part']);
        $this->assertSame('Hlubocska', $street['street']);

        // The three kinds of number stay apart: this outage names house numbers and nothing else.
        $this->assertSame('106, 107, 109, 110, 111, 126-131, 139', $street['house_nums']);
        $this->assertSame('', $street['ev_nums']);
        $this->assertSame('', $street['street_nums']);
    }

    /**
     * The announcement is built from the host this installation is configured with.
     *
     * @return void
     * @link \App\PowerOutages\Cez\CezPayloadNormalizer::fromTown()
     */
    public function testTheAnnouncementUsesTheConfiguredHost(): void
    {
        $outages = CezPayloadNormalizer::fromTown($this->townPayload(), 533165);

        $this->assertSame(
            'https://cdn.example.com/pdf/301289778-d9ulv4tct0gcmo4g0kpg.pdf',
            $outages[0]->announcementUrl,
        );
    }

    /**
     * An outage read by supply point, whose day and hours are written apart and without a zone.
     *
     * @return void
     * @link \App\PowerOutages\Cez\CezPayloadNormalizer::fromEan()
     */
    public function testAnOutageReadBySupplyPoint(): void
    {
        $outages = CezPayloadNormalizer::fromEan($this->eanPayload(), '859182400000001231');

        $this->assertCount(1, $outages);
        $outage = $outages[0];

        $this->assertSame('110061107633', $outage->outageNumber);

        // The hours are the ones on the announcement, which are wall-clock hours here. Read as
        // coordinated universal time they would be shown two hours late in summer.
        $this->assertSame(
            '2026-09-07 07:30:00',
            $outage->beginsAt?->setTimezone('Europe/Prague')->format('Y-m-d H:i:s'),
        );
        $this->assertSame(
            '2026-09-07 15:00:00',
            $outage->endsAt?->setTimezone('Europe/Prague')->format('Y-m-d H:i:s'),
        );

        $this->assertFalse($outage->cancelled);
        $this->assertSame('Kolin - Kolin IV, okres Kolin', $outage->summary);
        $this->assertSame(
            [['cadastral_code' => '668150', 'plot' => '5152/6']],
            $outage->places['parcels'],
        );
        $this->assertSame('21', $outage->places['streets'][0]['house_nums']);
    }

    /**
     * A withdrawal, which only the reading by supply point ever mentions.
     *
     * @return void
     * @link \App\PowerOutages\Cez\CezPayloadNormalizer::fromEan()
     */
    public function testAWithdrawalIsRead(): void
    {
        $payload = $this->eanPayload();
        $payload['data'][0]['cancelled'] = true;
        $payload['data'][0]['cancelDate'] = '2026-09-01T00:00:00';

        $outages = CezPayloadNormalizer::fromEan($payload, '859182400000001231');

        $this->assertTrue($outages[0]->cancelled);
        $this->assertSame('2026-09-01', $outages[0]->cancelledAt?->format('Y-m-d'));
    }

    /**
     * The two readings are of one outage, which is what lets them be put together.
     *
     * @return void
     * @link \App\PowerOutages\Dto\PowerOutageData::mergedWith()
     */
    public function testTheTwoReadingsAgreeOnTheNumberAndAreMerged(): void
    {
        $payload = $this->townPayload();
        $payload['outages_in_town'][0]['id'] = '110061107633';

        $byTown = CezPayloadNormalizer::fromTown($payload, 533165)[0];
        $byEan = CezPayloadNormalizer::fromEan($this->eanPayload(), '859182400000001231')[0];

        $this->assertSame($byTown->outageNumber, $byEan->outageNumber);

        $merged = $byTown->mergedWith($byEan);

        // The announcement and the municipality come from the one reading that carries them, the
        // hours from the one that publishes them, and the parcels are added up rather than picked.
        $this->assertSame($byTown->announcementUrl, $merged->announcementUrl);
        $this->assertSame(533165, $merged->townCode);
        $this->assertSame($byEan->beginsAt?->format('c'), $merged->beginsAt?->format('c'));
        $this->assertCount(1, $merged->places['parcels'], 'The same parcel was kept twice.');
    }

    /**
     * An outage that runs past midnight ends on the following day.
     *
     * @return void
     * @link \App\PowerOutages\Cez\CezPayloadNormalizer::fromEan()
     */
    public function testAnOutageRunningPastMidnightEndsTheNextDay(): void
    {
        $payload = $this->eanPayload();
        $payload['data'][0]['fromTime'] = '1970-01-01T22:00:00';
        $payload['data'][0]['toTime'] = '1970-01-01T02:00:00';

        $outage = CezPayloadNormalizer::fromEan($payload, '859182400000001231')[0];

        $this->assertSame('2026-09-07', $outage->beginsAt?->setTimezone('Europe/Prague')->format('Y-m-d'));
        $this->assertSame('2026-09-08', $outage->endsAt?->setTimezone('Europe/Prague')->format('Y-m-d'));
    }

    /**
     * An entry with no number of its own is passed over rather than stopping the reading.
     *
     * @return void
     * @link \App\PowerOutages\Cez\CezPayloadNormalizer::fromTown()
     */
    public function testAnOutageWithoutANumberIsPassedOver(): void
    {
        $payload = $this->townPayload();
        unset($payload['outages_in_town'][0]['id']);

        $this->assertSame([], CezPayloadNormalizer::fromTown($payload, 533165));
    }

    /**
     * What the widget answers about one municipality.
     *
     * @return array<string, mixed>
     */
    private function townPayload(): array
    {
        return [
            'outages' => null,
            'outages_in_town' => [
                [
                    'id' => '110061112294',
                    'announcement_key' => 'pdf/301289778-d9ulv4tct0gcmo4g0kpg.pdf',
                    'opened_at' => '2026-09-03T06:00:00Z',
                    'fix_expected_at' => '2026-09-03T11:00:00Z',
                    'addresses' => [
                        'towns' => [
                            [
                                'name' => 'Kolin',
                                'code' => 533165,
                                'district' => 'Kolin',
                                'cadastral_territories' => [
                                    [
                                        'name' => 'Kolin',
                                        'code' => 668150,
                                        'plots' => [
                                            ['cadastral_code' => '668150', 'plot' => '5152/6'],
                                        ],
                                    ],
                                ],
                                'town_districts' => [
                                    [
                                        'name' => '',
                                        'code' => 0,
                                        'town_parts' => [
                                            [
                                                'name' => 'Kolin VI',
                                                'streets' => [
                                                    [
                                                        'name' => 'Hlubocska',
                                                        'house_nums' => '106, 107, 109, 110, 111, 126-131, 139',
                                                        'ev_nums' => '',
                                                        'street_nums' => '',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'orphan_territories' => null,
                    ],
                ],
            ],
        ];
    }

    /**
     * What the portal answers about one supply point.
     *
     * @return array<string, mixed>
     */
    private function eanPayload(): array
    {
        return [
            'data' => [
                [
                    'cancelled' => false,
                    'cancelDate' => null,
                    'date' => '2026-09-07T00:00:00',
                    'fromTime' => '1970-01-01T07:30:00',
                    'toTime' => '1970-01-01T15:00:00',
                    'timeFormatted' => '07:30 - 15:00',
                    'dateFormatted' => '07.09.2026',
                    'number' => '110061107633',
                    'parts' => [
                        [
                            'description' => 'Kolin - Kolin IV, okres Kolin',
                            'city' => 'Kolin',
                            'cityPart' => 'Kolin IV',
                            'zip' => '280 02',
                            'district' => 'Kolin',
                            'streets' => [
                                [
                                    'streetName' => 'Kutnohorska',
                                    'streetNumbers' => [
                                        [
                                            'buildingId' => '21',
                                            'streetId' => '',
                                            'parcelaId' => '',
                                            'cadastralTerritoryCode' => '',
                                        ],
                                        [
                                            'buildingId' => '',
                                            'streetId' => '',
                                            'parcelaId' => '5152/6',
                                            'cadastralTerritoryCode' => '668150',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'statusCode' => 200,
            'flashMessages' => [],
        ];
    }
}
