<?php
declare(strict_types=1);

namespace App\Test\TestCase\Snmp\Provider;

use App\Snmp\Provider\RouterosSnmpPayloadNormalizer;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use RuntimeException;

/**
 * App\Snmp\Provider\RouterosSnmpPayloadNormalizer Test Case
 *
 * What arrives from an agent is JSON with whatever the agent felt like putting in it, and what comes
 * out of here is handed to the ORM. So what is held on to is the difference between a field that was
 * not reported and one that was reported empty: the first has to stay absent, and the second has to
 * arrive as the type the column takes.
 */
#[UsesClass(RouterosSnmpPayloadNormalizer::class)]
class RouterosSnmpPayloadNormalizerTest extends TestCase
{
    /**
     * Address the reading is taken at when the payload does not name one.
     *
     * @var string
     */
    private const FALLBACK_HOST = '10.20.30.40';

    /**
     * A payload that is not shaped like a reading at all is refused rather than picked over for
     * whatever happens to be in it.
     *
     * @return void
     * @link \App\Snmp\Provider\RouterosSnmpPayloadNormalizer::normalize()
     */
    public function testAPayloadThatIsNotAReadingIsRefused(): void
    {
        $this->expectException(RuntimeException::class);

        RouterosSnmpPayloadNormalizer::normalize(['device' => []], self::FALLBACK_HOST);
    }

    /**
     * A device that does not say where it was reached is filed under the address it was reached at.
     *
     * @return void
     * @link \App\Snmp\Provider\RouterosSnmpPayloadNormalizer::normalize()
     */
    public function testADeviceThatDoesNotSayWhereItWasReachedIsFiledUnderWhereItWas(): void
    {
        $data = RouterosSnmpPayloadNormalizer::normalize([
            'device' => ['serial_number' => 'HFT08KQ4T6C'],
            'interfaces' => [],
            'ip_addresses' => [],
        ], self::FALLBACK_HOST);

        $this->assertSame(self::FALLBACK_HOST, $data->device['ip_address']);
    }

    /**
     * The fields a device did not report are absent rather than empty, so that nothing is written
     * over what an earlier reading found out.
     *
     * @return void
     * @link \App\Snmp\Provider\RouterosSnmpPayloadNormalizer::normalize()
     */
    public function testWhatADeviceDidNotReportComesOutAsNothing(): void
    {
        $data = RouterosSnmpPayloadNormalizer::normalize([
            'device' => ['serial_number' => 'HFT08KQ4T6C', 'ip_address' => self::FALLBACK_HOST],
            'interfaces' => [['interface_index' => 7]],
            'ip_addresses' => [['interface_index' => 7, 'ip_address' => '10.20.30.40']],
        ], self::FALLBACK_HOST);

        $this->assertNull($data->device['name']);
        $this->assertNull($data->interfaces[0]['name']);
        $this->assertNull($data->interfaces[0]['frequency']);
        $this->assertNull($data->ipAddresses[0]['name']);
    }

    /**
     * What a device did report arrives as the type the column takes, whichever way the agent wrote
     * it into its JSON.
     *
     * @return void
     * @link \App\Snmp\Provider\RouterosSnmpPayloadNormalizer::normalize()
     */
    public function testWhatADeviceDidReportArrivesAsTheTypeTheColumnTakes(): void
    {
        $data = RouterosSnmpPayloadNormalizer::normalize([
            'device' => ['serial_number' => 'HFT08KQ4T6C', 'ip_address' => self::FALLBACK_HOST],
            'interfaces' => [[
                'interface_index' => '7',
                'name' => 'wlan-sector-north',
                'frequency' => '5500',
                'noise_floor' => -103,
                'client_count' => '12',
            ]],
            'ip_addresses' => [['interface_index' => '7', 'ip_address' => '10.20.30.41', 'name' => 'bridge']],
        ], self::FALLBACK_HOST);

        $this->assertSame(7, $data->interfaces[0]['interface_index']);
        $this->assertSame('wlan-sector-north', $data->interfaces[0]['name']);
        $this->assertSame(5500, $data->interfaces[0]['frequency']);
        $this->assertSame(-103, $data->interfaces[0]['noise_floor']);
        $this->assertSame(12, $data->interfaces[0]['client_count']);
        $this->assertSame(7, $data->ipAddresses[0]['interface_index']);
        $this->assertSame('bridge', $data->ipAddresses[0]['name']);
    }

    /**
     * The interfaces come out as a list, whatever the agent keyed them by - the ORM is handed them
     * in order rather than under whatever numbers the payload happened to carry.
     *
     * @return void
     * @link \App\Snmp\Provider\RouterosSnmpPayloadNormalizer::normalize()
     */
    public function testTheInterfacesComeOutAsAListWhateverTheyArrivedKeyedBy(): void
    {
        $data = RouterosSnmpPayloadNormalizer::normalize([
            'device' => ['serial_number' => 'HFT08KQ4T6C', 'ip_address' => self::FALLBACK_HOST],
            'interfaces' => [3 => ['interface_index' => 3], 9 => ['interface_index' => 9]],
            'ip_addresses' => [5 => ['interface_index' => 3, 'ip_address' => '10.20.30.41']],
        ], self::FALLBACK_HOST);

        $this->assertSame([0, 1], array_keys($data->interfaces));
        $this->assertSame([0], array_keys($data->ipAddresses));
    }
}
