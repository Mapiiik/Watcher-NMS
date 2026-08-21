<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use App\Test\Traits\ConfigureTestTrait;
use Cake\Cache\Cache;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\Http\Client\Response;
use Cake\Http\TestSuite\HttpClientTrait;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\EmailTrait;
use Cake\TestSuite\TestCase;
use Override;

/**
 * App\Command\PowerOutagesUpdateCommand Test Case
 *
 * What is asked of the run here is mostly what it refuses to do. Sweeping is the dangerous half of
 * a mirror kept this way - the readings come back empty most nights, and an empty answer must
 * never be mistaken for the distributor having stopped publishing.
 */
class PowerOutagesUpdateCommandTest extends TestCase
{
    use ConfigureTestTrait;
    use ConsoleIntegrationTestTrait;
    use EmailTrait;
    use HttpClientTrait;

    /**
     * The mast standing somewhere the address registry has heard of.
     */
    private const KOLIN_ID = '3f6f6b19-6a0e-4a5b-9a4a-2c0f4d5e6a71';

    /**
     * What the fixture keeps the supply point of that mast under.
     */
    private const KOLIN_EAN = '859182400000001231';

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.AppUsers',
        'app.AccessPointTypes',
        'app.AccessPoints',
        'app.AccessPointSupplyAddresses',
        'app.PowerOutages',
        'app.PowerOutageScopes',
        'app.AccessPointPowerOutages',
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

        $this->withConfigure([
            'PowerOutages.enabled' => true,
            'PowerOutages.bezstavyUrl' => 'https://outages.example.com',
            'PowerOutages.bezstavyCdnUrl' => 'https://cdn.example.com',
            'PowerOutages.dipUrl' => 'https://portal.example.com/shutdown-search',
            'PowerOutages.userAgent' => 'Watcher NMS (tests)',
            'Report.emails' => ['operator@example.com'],
        ]);

        // The answers about a municipality are kept for a while, which would otherwise carry from
        // one of these tests into the next.
        Cache::clear();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    #[Override]
    protected function tearDown(): void
    {
        $this->restoreConfigure();

        parent::tearDown();
    }

    /**
     * The help names every way of calling it.
     *
     * @return void
     * @link \App\Command\PowerOutagesUpdateCommand::buildOptionParser()
     */
    public function testHelpNamesEveryOption(): void
    {
        $this->exec('power_outages_update --help');

        $this->assertExitSuccess();

        foreach (['--file', '--dry-run', '--rematch', '--resolve-only', '--force-resolve', '--access-point'] as $option) {
            $this->assertOutputContains($option);
        }
    }

    /**
     * An installation that has not asked for this does not go asking after it.
     *
     * @return void
     * @link \App\Command\PowerOutagesUpdateCommand::execute()
     */
    public function testItStaysOffUntilItIsTurnedOn(): void
    {
        $this->withConfigure(['PowerOutages.enabled' => false]);

        $this->exec('power_outages_update');

        $this->assertExitSuccess();
        $this->assertErrorContains('turned off');
    }

    /**
     * A supply point that is asked about, answered, and written down as an outage of that mast.
     *
     * @return void
     * @link \App\Command\PowerOutagesUpdateCommand::execute()
     */
    public function testAnOutageOfOurSupplyPointIsWrittenDownAsCertain(): void
    {
        $this->mockClientPost(
            'https://portal.example.com/shutdown-search',
            $this->jsonResponse(['statusCode' => 200, 'data' => [[
                'number' => '110061199999',
                'date' => '2099-09-07T00:00:00',
                'fromTime' => '1970-01-01T07:30:00',
                'toTime' => '1970-01-01T15:00:00',
                'cancelled' => false,
                'parts' => [['description' => 'Kolin', 'city' => 'Kolin']],
            ]]]),
        );

        $this->exec('power_outages_update --resolve-limit=0 --access-point=' . self::KOLIN_ID);

        $this->assertExitSuccess();

        $outage = $this->outages()->find()->where(['outage_number' => '110061199999'])->firstOrFail();

        $link = $this->links()->find()
            ->where(['access_point_id' => self::KOLIN_ID, 'power_outage_id' => $outage->get('id')])
            ->firstOrFail();

        $this->assertSame('certain', $link->get('certainty')->value);
        $this->assertSame('ean', $link->get('matched_by')->value);
    }

    /**
     * A municipality that answered has what it no longer names swept; one that did not is left be.
     *
     * The heart of the whole arrangement. Most municipalities answer with nothing most nights, so
     * sweeping cannot key off an empty answer - it has to key off which questions were answered.
     *
     * @return void
     * @link \App\PowerOutages\Service\PowerOutagesUpdateService::updateNow()
     */
    public function testOnlyWhatWasAnsweredAboutIsSwept(): void
    {
        // Two masts without a supply point, standing in two different municipalities.
        $this->putMastInTown(self::KOLIN_ID, 533165);
        $secondMast = $this->addMastInTown(569810);

        // Both fixture outages hang off the municipality that answers, and neither is named again.
        $this->moveScopesTo('town:533165');
        $this->addOutageOfTown('110061188888', 569810, 'town:569810');

        $this->mockClientGet(
            'https://outages.example.com/cezd/api/inspecttown/533165',
            $this->jsonResponse(['outages' => null]),
        );
        $this->mockClientGet(
            'https://outages.example.com/cezd/api/inspecttown/569810',
            $this->newClientResponse(500, []),
        );

        $this->exec('power_outages_update --resolve-limit=0');

        $this->assertExitSuccess();

        $this->assertSame(
            0,
            $this->outages()->find()->where(['town_code' => 533165])->count(),
            'The municipality that answered should have had its outages swept.',
        );
        $this->assertSame(
            1,
            $this->outages()->find()->where(['outage_number' => '110061188888'])->count(),
            'The municipality that would not answer should have kept its outages.',
        );

        $this->assertNotEmpty($secondMast);
    }

    /**
     * A run hardly anybody answered writes nothing and says so.
     *
     * @return void
     * @link \App\PowerOutages\Service\PowerOutagesUpdateService::updateNow()
     */
    public function testARunNobodyAnsweredChangesNothing(): void
    {
        $this->putMastInTown(self::KOLIN_ID, 533165);
        $this->moveScopesTo('town:533165');

        $before = $this->outages()->find()->count();

        $this->mockClientGet(
            'https://outages.example.com/cezd/api/inspecttown/533165',
            $this->newClientResponse(500, []),
        );

        $this->exec('power_outages_update --resolve-limit=0');

        $this->assertExitError();
        $this->assertMailCount(1);
        $this->assertSame($before, $this->outages()->find()->count());
    }

    /**
     * A dry run does all of it and keeps none of it.
     *
     * @return void
     * @link \App\Command\PowerOutagesUpdateCommand::execute()
     */
    public function testADryRunKeepsNothing(): void
    {
        $this->mockClientPost(
            'https://portal.example.com/shutdown-search',
            $this->jsonResponse(['statusCode' => 200, 'data' => [[
                'number' => '110061177777',
                'date' => '2099-09-07T00:00:00',
                'fromTime' => '1970-01-01T07:30:00',
                'toTime' => '1970-01-01T15:00:00',
                'parts' => [],
            ]]]),
        );

        $this->exec('power_outages_update --dry-run --resolve-limit=0 --access-point=' . self::KOLIN_ID);

        $this->assertExitSuccess();
        $this->assertSame(0, $this->outages()->find()->where(['outage_number' => '110061177777'])->count());
    }

    /**
     * A dry run does not keep the addresses it looked up either.
     *
     * Looking those up writes as surely as storing an outage does, and it happens before any of
     * the outages are read - so a dry run that wrapped only the writing of outages would quietly
     * keep half of what it did.
     *
     * @return void
     * @link \App\PowerOutages\Service\PowerOutagesUpdateService::updateNow()
     */
    public function testADryRunKeepsNoAddressesEither(): void
    {
        $addresses = TableRegistry::getTableLocator()->get('AccessPointSupplyAddresses');
        $addresses->deleteAll(['access_point_id' => self::KOLIN_ID]);

        // Somewhere to look them up, and one address to be found there.
        $this->withConfigure(['Addresses.url' => 'https://addresses.example.com', 'Addresses.key' => '']);
        $this->mockClientGet(
            'https://addresses.example.com/v1/reverse?' . http_build_query([
                'country' => 'cz',
                'lat' => 50.0281552,
                'lon' => 15.200344,
                'radius_m' => 500.0,
                'limit' => 10,
                'include' => 'raw',
            ]),
            $this->jsonResponse([[
                'registry_ref' => '21154996',
                'formatted_address' => 'Hlubocska 106, 28002 Kolin',
                'number_type' => 'house',
                'distance_m' => 42.0,
                'raw' => ['obec_kod' => 533165, 'ulice_nazev' => 'Hlubocska', 'cislo_domovni' => 106],
            ]]),
        );
        $this->mockClientPost(
            'https://portal.example.com/shutdown-search',
            $this->jsonResponse(['statusCode' => 200, 'data' => []]),
        );

        $this->exec(sprintf(
            'power_outages_update --dry-run --force-resolve --access-point=%s',
            self::KOLIN_ID,
        ));

        $this->assertExitSuccess();
        $this->assertSame(
            0,
            $addresses->find()->where(['access_point_id' => self::KOLIN_ID])->count(),
            'A dry run kept the addresses it looked up.',
        );
    }

    /**
     * Kept answers are replayed without anybody being asked anything.
     *
     * @return void
     * @link \App\Command\PowerOutagesUpdateCommand::execute()
     */
    public function testKeptAnswersAreReplayed(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'outages') ?: '';
        file_put_contents($file, (string)json_encode([
            'ean:' . self::KOLIN_EAN => ['statusCode' => 200, 'data' => [[
                'number' => '110061166666',
                'date' => '2099-09-07T00:00:00',
                'fromTime' => '1970-01-01T07:30:00',
                'toTime' => '1970-01-01T15:00:00',
                'parts' => [],
            ]]],
        ]));

        $this->exec(sprintf(
            'power_outages_update --resolve-limit=0 --access-point=%s --file=%s',
            self::KOLIN_ID,
            $file,
        ));

        unlink($file);

        $this->assertExitSuccess();
        $this->assertSame(1, $this->outages()->find()->where(['outage_number' => '110061166666'])->count());
    }

    /**
     * Working the links out again asks nobody anything.
     *
     * No client is mocked here at all, which is the assertion: a run that reached out would fail.
     *
     * @return void
     * @link \App\Command\PowerOutagesUpdateCommand::execute()
     */
    public function testRematchingReachesOutToNobody(): void
    {
        $this->exec('power_outages_update --rematch');

        $this->assertExitSuccess();
        $this->assertOutputContains('outages');
    }

    /**
     * Looking the addresses up and stopping there does not go on to ask about outages.
     *
     * @return void
     * @link \App\Command\PowerOutagesUpdateCommand::execute()
     */
    public function testResolvingOnlyStopsThere(): void
    {
        $this->exec('power_outages_update --resolve-only --resolve-limit=0');

        $this->assertExitSuccess();
    }

    /**
     * Put a mast in one municipality and take its supply point away, so it is asked about by
     * municipality rather than directly.
     *
     * @param string $accessPointId The mast.
     * @param int $townCode The municipality.
     * @return void
     */
    private function putMastInTown(string $accessPointId, int $townCode): void
    {
        $accessPoints = TableRegistry::getTableLocator()->get('AccessPoints');
        $accessPoint = $accessPoints->get($accessPointId);
        $accessPoint->set('electricity_ean', null);
        $accessPoints->saveOrFail($accessPoint);

        $addresses = TableRegistry::getTableLocator()->get('AccessPointSupplyAddresses');
        $addresses->updateAll(['town_code' => $townCode], ['access_point_id' => $accessPointId]);
    }

    /**
     * A second mast, standing in another municipality.
     *
     * @param int $townCode The municipality.
     * @return string The id of the mast.
     */
    private function addMastInTown(int $townCode): string
    {
        $accessPoints = TableRegistry::getTableLocator()->get('AccessPoints');
        $accessPoint = $accessPoints->newEntity([
            'name' => 'Hradec Kralove mast',
            'gps_x' => 15.8327,
            'gps_y' => 50.2092,
        ]);
        $accessPoint->set('supply_resolved', '2026-08-20 00:00:00');
        $accessPoint->set('supply_resolved_gps_x', 15.8327);
        $accessPoint->set('supply_resolved_gps_y', 50.2092);
        $accessPoints->saveOrFail($accessPoint);

        $addresses = TableRegistry::getTableLocator()->get('AccessPointSupplyAddresses');
        $addresses->saveOrFail($addresses->newEntity([
            'access_point_id' => $accessPoint->get('id'),
            'rank' => 1,
            'town_code' => $townCode,
            'town_name' => 'Hradec Kralove',
            'street_name' => 'Kopretinova',
            'house_number' => 1,
            'number_type' => 'house',
        ]));

        return (string)$accessPoint->get('id');
    }

    /**
     * Hang every outage of the fixture off one reading.
     *
     * @param string $scope The reading.
     * @return void
     */
    private function moveScopesTo(string $scope): void
    {
        $scopes = TableRegistry::getTableLocator()->get('PowerOutageScopes');
        $outageIds = $this->outages()->find()->all()->extract('id')->toList();

        $scopes->deleteAll([]);

        foreach ($outageIds as $outageId) {
            $scopes->saveOrFail($scopes->newEntity(['power_outage_id' => $outageId, 'scope' => $scope]));
        }
    }

    /**
     * One more outage, hung off a reading of its own.
     *
     * @param string $number What the distributor keeps it under.
     * @param int $townCode The municipality it is in.
     * @param string $scope The reading that saw it.
     * @return void
     */
    private function addOutageOfTown(string $number, int $townCode, string $scope): void
    {
        $outages = $this->outages();
        $outage = $outages->newEntity([
            'distributor' => 'CEZD',
            'outage_number' => $number,
            'town_code' => $townCode,
        ]);
        $outages->saveOrFail($outage);

        $scopes = TableRegistry::getTableLocator()->get('PowerOutageScopes');
        $scopes->saveOrFail($scopes->newEntity([
            'power_outage_id' => $outage->get('id'),
            'scope' => $scope,
        ]));
    }

    /**
     * @return \Cake\ORM\Table
     */
    private function outages(): Table
    {
        return TableRegistry::getTableLocator()->get('PowerOutages');
    }

    /**
     * @return \Cake\ORM\Table
     */
    private function links(): Table
    {
        return TableRegistry::getTableLocator()->get('AccessPointPowerOutages');
    }

    /**
     * The address registry answers with a bare list where the distributor answers with an object,
     * so this takes either.
     *
     * @param array<mixed> $body What is answered with.
     * @return \Cake\Http\Client\Response
     */
    private function jsonResponse(array $body): Response
    {
        return $this->newClientResponse(200, ['Content-Type: application/json'], (string)json_encode($body));
    }
}
