<?php
declare(strict_types=1);

namespace App\Test\TestCase\Snmp\Dto;

use App\Snmp\Dto\RouterosSnmpData;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use RuntimeException;

/**
 * App\Snmp\Dto\RouterosSnmpData Test Case
 *
 * The two fields checked here are the ones a device is looked up by, so a reading that is missing
 * either of them has to be refused where it is built rather than further in, where it would be a
 * device saved over the wrong record or none at all.
 */
#[UsesClass(RouterosSnmpData::class)]
class RouterosSnmpDataTest extends TestCase
{
    /**
     * What a device answers with, with the two fields the tests vary named.
     *
     * @param string $serialNumber Serial number the device reports.
     * @param string $ipAddress Address the device reports being reached at.
     * @return array{
     *   serial_number: string,
     *   ip_address: string,
     *   name: string|null,
     *   system_description: string|null,
     *   board_name: string|null,
     *   software_version: string|null,
     *   firmware_version: string|null
     * }
     */
    private static function device(
        string $serialNumber = 'HFT08KQ4T6C',
        string $ipAddress = '10.20.30.40',
    ): array {
        return [
            'serial_number' => $serialNumber,
            'ip_address' => $ipAddress,
            'name' => 'ap-hilltop',
            'system_description' => 'RouterOS',
            'board_name' => 'RB5009',
            'software_version' => '7.14',
            'firmware_version' => '7.14',
        ];
    }

    /**
     * A complete reading is kept as it was read.
     *
     * @return void
     * @link \App\Snmp\Dto\RouterosSnmpData::__construct()
     */
    public function testACompleteReadingIsKeptAsItWasRead(): void
    {
        $data = new RouterosSnmpData(self::device(), [], []);

        $this->assertSame('HFT08KQ4T6C', $data->device['serial_number']);
        $this->assertSame([], $data->interfaces);
        $this->assertSame([], $data->ipAddresses);
    }

    /**
     * A reading without the serial number the device is matched by is refused.
     *
     * @return void
     * @link \App\Snmp\Dto\RouterosSnmpData::__construct()
     */
    public function testAReadingWithoutASerialNumberIsRefused(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('device.serial_number');

        new RouterosSnmpData(self::device(serialNumber: ''), [], []);
    }

    /**
     * A reading without the address it was taken at is refused.
     *
     * @return void
     * @link \App\Snmp\Dto\RouterosSnmpData::__construct()
     */
    public function testAReadingWithoutAnAddressIsRefused(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('device.ip_address');

        new RouterosSnmpData(self::device(ipAddress: ''), [], []);
    }
}
