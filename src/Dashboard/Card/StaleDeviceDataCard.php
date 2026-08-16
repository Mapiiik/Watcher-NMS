<?php
declare(strict_types=1);

namespace App\Dashboard\Card;

use App\Model\Enum\MaximumAge;
use App\Model\Table\RouterosDevicesTable;
use Override;

/**
 * Devices the agent has not written for a while.
 *
 * Nothing here says whether a device is up - what the agents write is only ever as true as
 * the last reading, so how long ago that was is the closest thing to a health signal the
 * inventory holds.
 */
class StaleDeviceDataCard extends AbstractDashboardCard
{
    /**
     * @param \App\Model\Table\RouterosDevicesTable $devices RouterOS devices table.
     */
    public function __construct(private RouterosDevicesTable $devices)
    {
    }

    /**
     * @return string
     */
    #[Override]
    public function id(): string
    {
        return 'stale_device_data';
    }

    /**
     * @return string
     */
    #[Override]
    public function title(): string
    {
        return __('Devices with Stale Data');
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function data(): array
    {
        $maximum_age = MaximumAge::FALLBACK;

        $query = $this->devices
            ->find()
            ->contain(['AccessPoints'])
            ->where(['RouterosDevices.modified <' => $maximum_age->since()])
            ->orderBy(['RouterosDevices.modified' => 'ASC']);

        $total = $query->count();

        return [
            'devices' => $query->limit($this->maximumRows())->all(),
            'total' => $total,
            'maximum_age' => $maximum_age,
        ];
    }
}
