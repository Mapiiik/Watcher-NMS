<?php
declare(strict_types=1);

namespace App\Test\TestCase\PowerOutages\Service;

use App\Model\Entity\AccessPoint;
use App\Model\Table\AccessPointsTable;
use App\Model\Table\AccessPointSupplyAddressesTable;
use App\PowerOutages\Service\AccessPointLocationResolver;
use Cake\Core\Configure;
use Cake\Http\TestSuite\HttpClientTrait;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Override;

/**
 * App\PowerOutages\Service\AccessPointLocationResolver Test Case
 */
class AccessPointLocationResolverTest extends TestCase
{
    use HttpClientTrait;

    /**
     * The mast standing somewhere the address registry has heard of.
     */
    private const KOLIN_ID = '3f6f6b19-6a0e-4a5b-9a4a-2c0f4d5e6a71';

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
     * @var \App\Model\Table\AccessPointsTable
     */
    private AccessPointsTable $AccessPoints;

    /**
     * @var \App\Model\Table\AccessPointSupplyAddressesTable
     */
    private AccessPointSupplyAddressesTable $SupplyAddresses;

    /**
     * setUp method
     *
     * @return void
     */
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        Configure::write('Addresses.url', 'https://addresses.example.com');
        Configure::write('Addresses.key', 'test-key');

        /** @var \App\Model\Table\AccessPointsTable $accessPoints */
        $accessPoints = TableRegistry::getTableLocator()->get('AccessPoints');
        $this->AccessPoints = $accessPoints;

        /** @var \App\Model\Table\AccessPointSupplyAddressesTable $supplyAddresses */
        $supplyAddresses = TableRegistry::getTableLocator()->get('AccessPointSupplyAddresses');
        $this->SupplyAddresses = $supplyAddresses;
    }

    /**
     * What the registry answers is taken apart into the fields the comparison later needs.
     *
     * The registry numbers are the point of asking it at all: the municipality is what the
     * distributor is asked about, and the two kinds of building number are what an outage is
     * compared against.
     *
     * @return void
     * @link \App\PowerOutages\Service\AccessPointLocationResolver::resolve()
     */
    public function testTheRegistryNumbersAreKept(): void
    {
        $this->mockRegistry([$this->match()]);

        $found = $this->resolver()->resolve($this->accessPoint());

        $this->assertSame(1, $found);

        $address = $this->SupplyAddresses->find()
            ->where(['access_point_id' => self::KOLIN_ID])
            ->orderBy(['rank' => 'ASC'])
            ->firstOrFail();

        $this->assertSame(533165, $address->get('town_code'));
        $this->assertSame('Hlubocska', $address->get('street_name'));
        $this->assertSame(106, $address->get('house_number'));
        $this->assertSame('house', $address->get('number_type'));
        $this->assertSame(42, $address->get('distance_metres'));
        $this->assertSame(1, $address->get('rank'));
    }

    /**
     * The addresses arrive nearest first and are numbered in that order.
     *
     * @return void
     * @link \App\PowerOutages\Service\AccessPointLocationResolver::resolve()
     */
    public function testTheAddressesAreNumberedNearestFirst(): void
    {
        $this->mockRegistry([
            $this->match(distance: 42.0, houseNumber: 106),
            $this->match(distance: 117.0, houseNumber: 108),
        ]);

        $this->assertSame(2, $this->resolver()->resolve($this->accessPoint()));

        $ranks = $this->SupplyAddresses->find()
            ->where(['access_point_id' => self::KOLIN_ID])
            ->orderBy(['rank' => 'ASC'])
            ->all()
            ->extract('house_number')
            ->toList();

        $this->assertSame([106, 108], $ranks);
    }

    /**
     * A mast with no address within the radius is answered, and answered with nothing.
     *
     * Not a failure: it says this mast stands away from everything, which is exactly the mast that
     * needs its supply point filled in. Nothing is recorded as having gone wrong.
     *
     * @return void
     * @link \App\PowerOutages\Service\AccessPointLocationResolver::resolve()
     */
    public function testAMastWithNoAddressNearItIsNotAFailure(): void
    {
        $this->mockRegistry([]);

        $this->assertSame(0, $this->resolver()->resolve($this->accessPoint()));

        $accessPoint = $this->AccessPoints->get(self::KOLIN_ID);

        $this->assertNull($accessPoint->get('supply_resolution_failed'));
        $this->assertNotNull($accessPoint->get('supply_resolved'));
        $this->assertSame(0, $this->SupplyAddresses->find()->where(['access_point_id' => self::KOLIN_ID])->count());
    }

    /**
     * The registry being unreachable leaves what was found before it standing.
     *
     * @return void
     * @link \App\PowerOutages\Service\AccessPointLocationResolver::resolve()
     */
    public function testTheRegistryFailingLeavesTheOldAddressesAlone(): void
    {
        $before = $this->SupplyAddresses->find()->where(['access_point_id' => self::KOLIN_ID])->count();
        $this->assertGreaterThan(0, $before, 'The fixture should carry addresses to be kept.');

        $this->mockClientGet($this->registryUrl(), $this->newClientResponse(503, []));

        $this->assertSame(-1, $this->resolver()->resolve($this->accessPoint()));

        $this->assertSame(
            $before,
            $this->SupplyAddresses->find()->where(['access_point_id' => self::KOLIN_ID])->count(),
        );
        $this->assertNotNull($this->AccessPoints->get(self::KOLIN_ID)->get('supply_resolution_failed'));
    }

    /**
     * An installation that was never given an address registry is not a registry that is down,
     * and the mast is left exactly as a failure would leave it - but the reason written against it
     * says which of the two it was, because only one of them is worth waiting out.
     *
     * @return void
     * @link \App\PowerOutages\Service\AccessPointLocationResolver::resolve()
     */
    public function testWithNoRegistryAtAllTheMastIsLeftAloneToo(): void
    {
        Configure::write('Addresses.url', '');

        $before = $this->SupplyAddresses->find()->where(['access_point_id' => self::KOLIN_ID])->count();

        $this->assertSame(-1, $this->resolver()->resolve($this->accessPoint()));

        $this->assertSame(
            $before,
            $this->SupplyAddresses->find()->where(['access_point_id' => self::KOLIN_ID])->count(),
        );
        $this->assertSame(
            'The national address registry is not configured.',
            $this->AccessPoints->get(self::KOLIN_ID)->get('supply_resolution_failed'),
        );
    }

    /**
     * A mast whose coordinates nobody has set cannot be looked up, and says so.
     *
     * @return void
     * @link \App\PowerOutages\Service\AccessPointLocationResolver::resolve()
     */
    public function testAMastWithNoCoordinatesSaysSo(): void
    {
        $accessPoint = $this->accessPoint();
        $accessPoint->set('gps_x', null);
        $accessPoint->set('gps_y', null);

        $this->assertSame(-1, $this->resolver()->resolve($accessPoint));
        $this->assertNotNull($this->AccessPoints->get(self::KOLIN_ID)->get('supply_resolution_failed'));
    }

    /**
     * A mast that has been moved is looked up again; one that has not is left alone.
     *
     * @return void
     * @link \App\Model\Entity\AccessPoint::supplyAddressesAreStale()
     */
    public function testAMastThatHasMovedIsWorthLookingUpAgain(): void
    {
        $accessPoint = $this->accessPoint();

        $this->assertFalse($accessPoint->supplyAddressesAreStale());

        $accessPoint->set('gps_x', 15.3);

        $this->assertTrue($accessPoint->supplyAddressesAreStale());
    }

    /**
     * The resolver as these tests use it.
     *
     * @return \App\PowerOutages\Service\AccessPointLocationResolver
     */
    private function resolver(): AccessPointLocationResolver
    {
        return new AccessPointLocationResolver(radiusMetres: 500, limit: 10);
    }

    /**
     * The mast the tests look up.
     *
     * @return \App\Model\Entity\AccessPoint
     */
    private function accessPoint(): AccessPoint
    {
        return $this->AccessPoints->get(self::KOLIN_ID);
    }

    /**
     * @param list<array<string, mixed>> $matches What the registry answers with.
     * @return void
     */
    private function mockRegistry(array $matches): void
    {
        $this->mockClientGet(
            $this->registryUrl(),
            $this->newClientResponse(200, ['Content-Type: application/json'], (string)json_encode($matches)),
        );
    }

    /**
     * Where the registry is asked, spelled the way the client spells it.
     *
     * @return string
     */
    private function registryUrl(): string
    {
        return 'https://addresses.example.com/v1/reverse?' . http_build_query([
            'country' => 'cz',
            'lat' => 50.0281552,
            'lon' => 15.200344,
            'radius_m' => 500.0,
            'limit' => 10,
            'include' => 'raw',
        ]);
    }

    /**
     * One address as the registry answers it, with the registry numbers under `raw`.
     *
     * @param float $distance How far it stands from the mast.
     * @param int $houseNumber The number of the building.
     * @return array<string, mixed>
     */
    private function match(float $distance = 42.0, int $houseNumber = 106): array
    {
        return [
            'registry_ref' => '21154996',
            'source' => 'cz',
            'street' => 'Hlubocska',
            'house_number' => (string)$houseNumber,
            'number_type' => 'house',
            'city' => 'Kolin',
            'postal_code' => '28002',
            'formatted_address' => 'Hlubocska ' . $houseNumber . ', 28002 Kolin',
            'geometry' => ['type' => 'Point', 'coordinates' => [15.200344, 50.0281552]],
            'distance_m' => $distance,
            'score' => null,
            'raw' => [
                'kod_adm' => 21154996,
                'obec_kod' => 533165,
                'obec_nazev' => 'Kolin',
                'cast_obce_nazev' => 'Kolin VI',
                'ulice_nazev' => 'Hlubocska',
                'cislo_domovni' => $houseNumber,
                'cislo_orientacni' => null,
                'cislo_orientacni_znak' => null,
                'psc' => 28002,
            ],
        ];
    }
}
