<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\AccessPoint;
use App\Test\Traits\ConfigureTestTrait;
use Cake\Cache\Cache;
use Cake\Http\TestSuite\HttpClientTrait;
use Cake\TestSuite\TestCase;
use Maps\Geocoder\AddressRegistryGeocoder;
use Override;

/**
 * App\Model\Entity\AccessPoint Test Case
 *
 * The address the geocoder finds is shown beside the mast, and the number the registry keeps that
 * address under is what somebody is sent to the distributor with. The two come out of one lookup,
 * so both are asked about here.
 */
class AccessPointTest extends TestCase
{
    use ConfigureTestTrait;
    use HttpClientTrait;

    /**
     * Where the registry answers, as far as these tests are concerned.
     */
    private const REGISTRY_URL = 'https://addresses.example.com';

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
            'Maps.geocoder' => AddressRegistryGeocoder::class,
            'Maps.addressRegistry.url' => self::REGISTRY_URL,
            'Maps.addressRegistry.key' => 'test-key',
            'Maps.addressRegistry.defaultCountries' => 'cz',
        ]);

        // The lookup is cached against the access point, so one test must not answer the next.
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
        Cache::clear();

        parent::tearDown();
    }

    /**
     * A Czech address hands over both the wording and the number it is filed under.
     *
     * @return void
     * @link \App\Model\Entity\AccessPoint::getNearestAddressRegistryNumber()
     */
    public function testACzechAddressGivesItsRegistryNumber(): void
    {
        $this->mockRegistry('cz', '21154996');

        $accessPoint = $this->accessPoint();

        $this->assertSame('Karlovo namesti 91, 28002 Kolin', $accessPoint->getNearestFoundAddress());
        $this->assertSame('21154996', $accessPoint->getNearestAddressRegistryNumber());
    }

    /**
     * An address the registry places in another country hands over no number.
     *
     * The number is only ever handed to a Czech distributor, so one naming a record somewhere else
     * is worse than none: it would send somebody to a page that cannot answer about that mast.
     *
     * @return void
     * @link \App\Model\Entity\AccessPoint::getNearestAddressRegistryNumber()
     */
    public function testAnAddressAbroadGivesNoRegistryNumber(): void
    {
        $this->mockRegistry('hr', 'HR.DGU.RPJ:KB.0000021409');

        $this->assertNull($this->accessPoint()->getNearestAddressRegistryNumber());
    }

    /**
     * A geocoder that names no record at all hands over nothing.
     *
     * @return void
     * @link \App\Model\Entity\AccessPoint::getNearestAddressRegistryNumber()
     */
    public function testAnAddressWithoutAReferenceGivesNoRegistryNumber(): void
    {
        $this->mockRegistry(null, null);

        $this->assertNull($this->accessPoint()->getNearestAddressRegistryNumber());
    }

    /**
     * A mast nobody has placed is not looked up at all.
     *
     * @return void
     * @link \App\Model\Entity\AccessPoint::getNearestAddressRegistryNumber()
     */
    public function testAMastWithoutCoordinatesIsNotLookedUp(): void
    {
        $accessPoint = new AccessPoint(['id' => 'no-coordinates', 'gps_x' => null, 'gps_y' => null]);

        $this->assertNull($accessPoint->nearestAddressSuggestion());
        $this->assertNull($accessPoint->getNearestAddressRegistryNumber());
    }

    /**
     * The mast the tests look up, standing in Kolin.
     *
     * @return \App\Model\Entity\AccessPoint
     */
    private function accessPoint(): AccessPoint
    {
        return new AccessPoint([
            'id' => 'b1a5f0c2-0000-4000-8000-000000000001',
            'gps_x' => 15.2003440,
            'gps_y' => 50.0281552,
        ]);
    }

    /**
     * What the registry answers about that place.
     *
     * @param string|null $source Which country's register the record came out of.
     * @param string|null $registryRef What that register keeps it under.
     * @return void
     */
    private function mockRegistry(?string $source, ?string $registryRef): void
    {
        $match = [
            'formatted_address' => 'Karlovo namesti 91, 28002 Kolin',
            'geometry' => ['type' => 'Point', 'coordinates' => [15.200344, 50.0281552]],
            'score' => null,
        ];

        if ($source !== null && $registryRef !== null) {
            $match['source'] = $source;
            $match['registry_ref'] = $registryRef;
        }

        $this->mockClientGet(
            self::REGISTRY_URL . '/v1/reverse?' . http_build_query([
                'country' => 'cz',
                'lat' => 50.0281552,
                'lon' => 15.200344,
                'limit' => 1,
            ]),
            $this->newClientResponse(
                200,
                ['Content-Type: application/json'],
                (string)json_encode([$match]),
            ),
        );
    }
}
