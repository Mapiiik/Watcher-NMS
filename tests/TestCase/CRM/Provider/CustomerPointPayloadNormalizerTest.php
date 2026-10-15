<?php
declare(strict_types=1);

namespace App\Test\TestCase\CRM\Provider;

use App\CRM\Dto\CustomerPoint;
use App\CRM\Provider\CustomerPointPayloadNormalizer;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\CRM\Provider\CustomerPointPayloadNormalizer Test Case
 */
#[UsesClass(CustomerPointPayloadNormalizer::class)]
#[UsesClass(CustomerPoint::class)]
class CustomerPointPayloadNormalizerTest extends TestCase
{
    /**
     * A point with a connection and an address, as the CRM lists one.
     *
     * @return void
     * @link \App\CRM\Provider\CustomerPointPayloadNormalizer::customerPoints()
     */
    public function testAPointIsRead(): void
    {
        $points = CustomerPointPayloadNormalizer::customerPoints([
            [
                'name' => 'Main Street 1',
                'gps_y' => 50.1,
                'gps_x' => 14.4,
                'note' => 'RUIAN: 12345',
                'CustomerConnections' => [
                    [
                        'name' => 'John Doe',
                        'customer_number' => 1001,
                        'customer_url' => '/customers/view/1',
                        'contract_number' => 'C-1001',
                        'contract_url' => '/contracts/view/1',
                        'access_point_id' => '1b0e5f7c-0c4f-4a43-9a6e-0f2a4f1d2c3b',
                        'note' => null,
                        'CustomerConnectionIps' => [
                            ['ip_address' => '10.0.0.1', 'name' => 'router', 'note' => 'router'],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertCount(1, $points);
        $point = $points[0];
        $this->assertSame(14.4, $point->gpsX);
        $this->assertSame(50.1, $point->gpsY);
        $this->assertSame('Main Street 1', $point->name);
        $this->assertTrue($point->hasLocation());

        $this->assertCount(1, $point->connections);
        $connection = $point->connections[0];
        $this->assertSame('1001', $connection->customerNumber);
        $this->assertSame('C-1001', $connection->contractNumber);
        $this->assertSame('1b0e5f7c-0c4f-4a43-9a6e-0f2a4f1d2c3b', $connection->accessPointId);
        $this->assertNull($connection->note);

        $this->assertCount(1, $connection->ips);
        $this->assertSame('10.0.0.1', $connection->ips[0]->ipAddress);
        $this->assertSame('router', $connection->ips[0]->name);
    }

    /**
     * A point the CRM could not place still carries its connections, it only says nowhere.
     *
     * @return void
     * @link \App\CRM\Dto\CustomerPoint::hasLocation()
     */
    public function testAPointWithoutCoordinatesHasNoLocation(): void
    {
        $points = CustomerPointPayloadNormalizer::customerPoints([
            ['gps_x' => null, 'gps_y' => 'not a number', 'CustomerConnections' => []],
            ['gps_x' => 0, 'gps_y' => 0, 'CustomerConnections' => []],
        ]);

        $this->assertNull($points[0]->gpsX);
        $this->assertNull($points[0]->gpsY);
        $this->assertFalse($points[0]->hasLocation());
        $this->assertFalse($points[1]->hasLocation());
    }

    /**
     * A listing missing what its structure hangs on is refused as a whole.
     *
     * @param array<mixed> $payload The listing.
     * @param string $message What it is refused with.
     * @return void
     * @link \App\CRM\Provider\CustomerPointPayloadNormalizer::customerPoints()
     */
    #[DataProvider('brokenProvider')]
    public function testABrokenListingIsRefused(array $payload, string $message): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        CustomerPointPayloadNormalizer::customerPoints($payload);
    }

    /**
     * @return array<string, array{array<mixed>, string}>
     */
    public static function brokenProvider(): array
    {
        $connection = ['customer_number' => '1', 'contract_number' => 'C-1', 'CustomerConnectionIps' => []];

        return [
            'a point that is not one' => [['text'], 'CustomerPoint must be an object.'],
            'no coordinates' => [[['CustomerConnections' => []]], 'gps_x and gps_y must exist.'],
            'no connections' => [[['gps_x' => 1, 'gps_y' => 1]], 'CustomerConnections must be an array.'],
            'no customer' => [
                [['gps_x' => 1, 'gps_y' => 1, 'CustomerConnections' => [
                    ['contract_number' => 'C-1', 'CustomerConnectionIps' => []],
                ]]],
                'customer_number is missing.',
            ],
            'no addresses' => [
                [['gps_x' => 1, 'gps_y' => 1, 'CustomerConnections' => [
                    ['customer_number' => '1', 'contract_number' => 'C-1'],
                ]]],
                'CustomerConnectionIps must be an array.',
            ],
            'an address without the address' => [
                [['gps_x' => 1, 'gps_y' => 1, 'CustomerConnections' => [
                    ['CustomerConnectionIps' => [['name' => 'x']]] + $connection,
                ]]],
                'ip_address is missing.',
            ],
            'broken after a good point' => [
                [
                    ['gps_x' => 1, 'gps_y' => 1, 'CustomerConnections' => [$connection]],
                    ['gps_x' => 1, 'gps_y' => 1, 'CustomerConnections' => ['text']],
                ],
                'CustomerConnection must be an object.',
            ],
        ];
    }
}
