<?php
declare(strict_types=1);

namespace App\Dashboard\Card;

use App\Model\Table\RadarInterferencesTable;
use Override;

/**
 * Our own devices that the weather radar register reports as interfering with it.
 *
 * The register names everybody's, and only the ones matched to a device of ours are
 * something we can act on - the same join the device listing is drawn from.
 */
class RadarInterferencesCard extends AbstractDashboardCard
{
    /**
     * @param \App\Model\Table\RadarInterferencesTable $interferences Radar interferences table.
     */
    public function __construct(private RadarInterferencesTable $interferences)
    {
    }

    /**
     * @return string
     */
    #[Override]
    public function id(): string
    {
        return 'radar_interferences';
    }

    /**
     * @return string
     */
    #[Override]
    public function title(): string
    {
        return __('Our Devices Interfering with Radar');
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function data(): array
    {
        $query = $this->interferences->find();

        $query
            ->join([
                'RouterosDeviceInterfaces' => [
                    'table' => 'routeros_device_interfaces',
                    'type' => 'INNER',
                    'conditions' => 'RadarInterferences.mac_address = RouterosDeviceInterfaces.mac_address',
                ],
                'RouterosDevices' => [
                    'table' => 'routeros_devices',
                    'type' => 'INNER',
                    'conditions' => 'RouterosDeviceInterfaces.routeros_device_id = RouterosDevices.id',
                ],
            ])
            ->select($this->interferences)
            ->select([
                'routeros_device_id' => 'RouterosDevices.id',
                'routeros_device_name' => 'RouterosDevices.name',
                'routeros_device_interface_name' => 'RouterosDeviceInterfaces.name',
            ])
            ->orderBy(['RadarInterferences.name' => 'ASC']);

        $total = $query->count();

        return [
            'interferences' => $query->limit($this->maximumRows())->all(),
            'total' => $total,
        ];
    }
}
