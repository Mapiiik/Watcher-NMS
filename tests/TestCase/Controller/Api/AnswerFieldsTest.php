<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Test\Traits\ControllerTestTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * What the actions the other applications reach send out, field by field.
 *
 * These answers are entities turned into JSON, so a column added to a table would go out with them
 * on its own. A new field has to be written down here first, which is the moment to ask whether it
 * should leave the application at all.
 */
class AnswerFieldsTest extends TestCase
{
    use ControllerTestTrait;
    use IntegrationTestTrait;

    /**
     * An access point.
     *
     * @var list<string>
     */
    private const ACCESS_POINT = [
        'access_point_type_id',
        'archived',
        'archived_by',
        'contract_conditions',
        'created',
        'created_by',
        'device_name',
        'electricity_ean',
        'electricity_meter_number',
        'gps_x',
        'gps_y',
        'id',
        'modified',
        'modified_by',
        'month_of_electricity_meter_reading',
        'name',
        'note',
        'parent_access_point_id',
        'supply_resolution_failed',
        'supply_resolved',
        'supply_resolved_gps_x',
        'supply_resolved_gps_y',
    ];

    /**
     * An IP address range, as it is itself.
     *
     * @var list<string>
     */
    private const RANGE = [
        'access_point_id',
        'created',
        'created_by',
        'for_customer_addresses_set_manually',
        'for_customer_addresses_set_via_radius',
        'for_customer_networks_set_manually',
        'for_customer_networks_set_via_radius',
        'for_subnets',
        'for_technology_addresses_set_manually',
        'for_technology_networks_set_manually',
        'id',
        'ip_gateway',
        'ip_network',
        'modified',
        'modified_by',
        'name',
        'note',
        'parent_ip_address_range_id',
    ];

    /**
     * A RouterOS device.
     *
     * @var list<string>
     */
    private const ROUTEROS_DEVICE = [
        'access_point_id',
        'board_name',
        'created',
        'created_by',
        'customer_connection_id',
        'device_type_id',
        'firmware_version',
        'id',
        'ip_address',
        'modified',
        'modified_by',
        'name',
        'serial_number',
        'software_version',
        'system_description',
    ];

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.AccessPointTypes',
        'app.AccessPoints',
        'app.DeviceTypes',
        'app.CustomerPoints',
        'app.CustomerConnections',
        'app.IpAddressRanges',
        'app.RouterosDevices',
    ];

    /**
     * @param string $url The action.
     * @param string $key What the records are under in the answer.
     * @param list<string> $fields What a record is allowed to carry.
     * @return void
     */
    #[DataProvider('answers')]
    public function testOnlyTheFieldsWrittenDownGoOut(string $url, string $key, array $fields): void
    {
        $this->login('api');
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);

        $this->get($url);

        $this->assertResponseOk();
        $answer = json_decode($this->_getBodyAsString(), true);
        $records = $answer[$key];
        $this->assertNotEmpty($records, 'There is nothing to look at.');

        $this->assertSame($fields, $this->fieldsOf(array_is_list($records) ? $records : [$records]));
    }

    /**
     * @return array<string, array{string, string, list<string>}>
     */
    public static function answers(): array
    {
        $range = [
            ...self::RANGE,
            'parent_ip_address_range',
            ...self::nestedUnder('parent_ip_address_range', self::RANGE),
            'access_point',
            ...self::nestedUnder('access_point', self::ACCESS_POINT),
        ];
        sort($range);

        return [
            'access points' => ['/api/access-points.json', 'accessPoints', self::ACCESS_POINT],
            'ranges' => ['/api/ip-address-ranges.json', 'ipAddressRanges', $range],
            'devices' => ['/api/routeros-devices.json', 'routerosDevices', self::ROUTEROS_DEVICE],
        ];
    }

    /**
     * The fields with the path of what they are nested in.
     *
     * @param string $path Where they are.
     * @param list<string> $fields The fields.
     * @return list<string>
     */
    private static function nestedUnder(string $path, array $fields): array
    {
        return array_map(fn(string $field): string => $path . '.' . $field, $fields);
    }

    /**
     * The fields of the records, sorted, and those of what is nested in them with its path.
     *
     * @param list<array<string, mixed>> $records The records.
     * @param string $path Where they are.
     * @return list<string>
     */
    private function fieldsOf(array $records, string $path = ''): array
    {
        $fields = [];
        foreach ($records as $record) {
            foreach ($record as $field => $value) {
                $fields[] = $path . $field;
                if (is_array($value) && $value !== []) {
                    $nested = array_is_list($value) ? $value : [$value];
                    if (is_array($nested[0])) {
                        $fields = [...$fields, ...$this->fieldsOf($nested, $path . $field . '.')];
                    }
                }
            }
        }
        $fields = array_values(array_unique($fields));
        sort($fields);

        return $fields;
    }
}
