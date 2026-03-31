<?php
declare(strict_types=1);

namespace App\Snmp\Dto;

use RuntimeException;

final class RouterosSnmpData
{
    /**
     * @param array{
     *   serial_number: string,
     *   ip_address: string,
     *   name: string|null,
     *   system_description: string|null,
     *   board_name: string|null,
     *   software_version: string|null,
     *   firmware_version: string|null
     * } $device
     * @param list<array{
     *   interface_index: int,
     *   name: string|null,
     *   comment: string|null,
     *   interface_admin_status: int|null,
     *   interface_oper_status: int|null,
     *   interface_type: int|null,
     *   mac_address: string|null,
     *   ssid: string|null,
     *   bssid: string|null,
     *   band: string|null,
     *   frequency: int|null,
     *   noise_floor: int|null,
     *   client_count: int|null,
     *   overall_tx_ccq: int|null
     * }> $interfaces
     * @param list<array{
     *   interface_index: int,
     *   ip_address: string,
     *   name: string|null
     * }> $ipAddresses
     */
    public function __construct(
        public readonly array $device,
        public readonly array $interfaces,
        public readonly array $ipAddresses,
    ) {
        $this->assertValid();
    }

    /**
     * Basic validation to ensure required fields are present and correctly typed.
     */
    private function assertValid(): void
    {
        if (empty($this->device['serial_number'])) {
            throw new RuntimeException('SNMP data missing device.serial_number');
        }
        if (empty($this->device['ip_address'])) {
            throw new RuntimeException('SNMP data missing device.ip_address');
        }
    }
}
