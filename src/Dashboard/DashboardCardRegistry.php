<?php
declare(strict_types=1);

namespace App\Dashboard;

use App\Dashboard\Card\ElectricityMeterReadingsCard;
use App\Dashboard\Card\MyTasksCard;
use App\Dashboard\Card\PressingTasksCard;
use App\Dashboard\Card\RadarInterferencesCard;
use App\Dashboard\Card\StaleDeviceDataCard;
use App\Dashboard\Card\StaleTasksCard;
use App\Dashboard\Card\UnassignedTasksCard;
use App\Model\Table\AccessPointsTable;
use App\Model\Table\RadarInterferencesTable;
use App\Model\Table\RouterosDevicesTable;
use App\Model\Table\TasksTable;
use Cake\ORM\Locator\LocatorAwareTrait;
use Dashboard\Card\CardRegistryInterface;
use Dashboard\Card\DashboardCardInterface;

/**
 * Registry of the cards the dashboard can draw.
 *
 * This is the single extension point for cards: register one here and it appears, in this
 * order, for the roles it names. Cards are built lazily, so registering one costs nothing
 * until it is actually drawn.
 */
final class DashboardCardRegistry implements CardRegistryInterface
{
    use LocatorAwareTrait;

    /**
     * @var array<string, callable(): \Dashboard\Card\DashboardCardInterface>
     */
    private array $factories = [];

    /**
     * @param string|null $role The role of the signed-in operator.
     * @param string|null $user_id The signed-in operator.
     */
    public function __construct(private ?string $role, ?string $user_id)
    {
        /** @var \App\Model\Table\TasksTable $tasks */
        $tasks = $this->fetchTable(TasksTable::class);
        /** @var \App\Model\Table\RouterosDevicesTable $devices */
        $devices = $this->fetchTable(RouterosDevicesTable::class);
        /** @var \App\Model\Table\AccessPointsTable $access_points */
        $access_points = $this->fetchTable(AccessPointsTable::class);
        /** @var \App\Model\Table\RadarInterferencesTable $interferences */
        $interferences = $this->fetchTable(RadarInterferencesTable::class);

        $this->factories = [
            'pressing_tasks' => fn(): DashboardCardInterface => new PressingTasksCard($tasks),
            'my_tasks' => fn(): DashboardCardInterface => new MyTasksCard($tasks, $user_id),
            'unassigned_tasks' => fn(): DashboardCardInterface => new UnassignedTasksCard($tasks),
            'stale_tasks' => fn(): DashboardCardInterface => new StaleTasksCard($tasks),
            'stale_device_data' => fn(): DashboardCardInterface => new StaleDeviceDataCard($devices),
            'electricity_meter_readings' =>
                fn(): DashboardCardInterface => new ElectricityMeterReadingsCard($access_points),
            'radar_interferences' => fn(): DashboardCardInterface => new RadarInterferencesCard($interferences),
        ];
    }

    /**
     * The card registered under the given id, or null where there is none.
     *
     * @param string $id Registry key.
     * @return \Dashboard\Card\DashboardCardInterface|null
     */
    public function get(string $id): ?DashboardCardInterface
    {
        $factory = $this->factories[$id] ?? null;

        return $factory === null ? null : $factory();
    }

    /**
     * The card registered under the given id, but only where the signed-in role may see
     * it. Cards are fetched one URL at a time, so a card has to check who is asking.
     *
     * @param string $id Registry key.
     * @return \Dashboard\Card\DashboardCardInterface|null
     */
    public function getAllowed(string $id): ?DashboardCardInterface
    {
        $card = $this->get($id);

        return $card !== null && $this->isAllowed($card) ? $card : null;
    }

    /**
     * The cards the signed-in role is offered, in the order they are registered.
     *
     * @return list<\Dashboard\Card\DashboardCardInterface>
     */
    public function forRole(): array
    {
        $cards = [];
        foreach (array_keys($this->factories) as $id) {
            $card = $this->get($id);
            if ($card !== null && $this->isAllowed($card)) {
                $cards[] = $card;
            }
        }

        return $cards;
    }

    /**
     * Whether the signed-in role is offered the given card. Administrators are offered
     * every card, as they are everywhere else.
     *
     * @param \Dashboard\Card\DashboardCardInterface $card The card to ask about.
     * @return bool
     */
    private function isAllowed(DashboardCardInterface $card): bool
    {
        if ($this->role === 'admin') {
            return true;
        }

        $roles = $card->roles();

        return $roles === [] || ($this->role !== null && in_array($this->role, $roles, true));
    }
}
