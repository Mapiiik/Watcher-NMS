<?php
declare(strict_types=1);

namespace App\Dashboard;

use App\CRM\Tasks as CrmTasks;
use App\Dashboard\Card\Crm\MyTasksCard as CrmMyTasksCard;
use App\Dashboard\Card\Crm\PressingTasksCard as CrmPressingTasksCard;
use App\Dashboard\Card\Crm\StaleTasksCard as CrmStaleTasksCard;
use App\Dashboard\Card\Crm\UnassignedTasksCard as CrmUnassignedTasksCard;
use App\Dashboard\Card\ElectricityMeterReadingsCard;
use App\Dashboard\Card\PowerOutagesCard;
use App\Dashboard\Card\RadarInterferencesCard;
use App\Dashboard\Card\StaleDeviceDataCard;
use App\Model\Table\AccessPointPowerOutagesTable;
use App\Model\Table\AccessPointsTable;
use App\Model\Table\RadarInterferencesTable;
use App\Model\Table\RouterosDevicesTable;
use App\Model\Table\TasksTable;
use Cake\ORM\Locator\LocatorAwareTrait;
use Dashboard\Card\CardRegistryInterface;
use Dashboard\Card\DashboardCardInterface;
use Tasks\Dashboard\Card\MyTasksCard;
use Tasks\Dashboard\Card\PressingTasksCard;
use Tasks\Dashboard\Card\StaleTasksCard;
use Tasks\Dashboard\Card\UnassignedTasksCard;

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
     * @param string|null $username The signed-in operator, by the name the other application also
     *   knows them by - their identifiers here mean nothing over there.
     */
    public function __construct(private ?string $role, ?string $user_id, ?string $username = null)
    {
        /** @var \App\Model\Table\TasksTable $tasks */
        $tasks = $this->fetchTable(TasksTable::class);
        /** @var \App\Model\Table\RouterosDevicesTable $devices */
        $devices = $this->fetchTable(RouterosDevicesTable::class);
        /** @var \App\Model\Table\AccessPointsTable $access_points */
        $access_points = $this->fetchTable(AccessPointsTable::class);
        /** @var \App\Model\Table\RadarInterferencesTable $interferences */
        $interferences = $this->fetchTable(RadarInterferencesTable::class);
        /** @var \App\Model\Table\AccessPointPowerOutagesTable $power_outages */
        $power_outages = $this->fetchTable(AccessPointPowerOutagesTable::class);

        // The task cards keep their ids whichever application is answering for them, so an
        // operator who has arranged their dashboard keeps that arrangement when an installation
        // hands its tasks over.
        $taskCards = CrmTasks::areUsed()
            ? $this->crmTaskCards(new CrmTasks(), $username)
            : [
                'pressing_tasks' => fn(): DashboardCardInterface => new PressingTasksCard($tasks),
                'my_tasks' => fn(): DashboardCardInterface => new MyTasksCard($tasks, $user_id),
                'unassigned_tasks' => fn(): DashboardCardInterface => new UnassignedTasksCard($tasks),
                'stale_tasks' => fn(): DashboardCardInterface => new StaleTasksCard($tasks),
            ];

        $this->factories = $taskCards + [
            'stale_device_data' => fn(): DashboardCardInterface => new StaleDeviceDataCard($devices),
            'electricity_meter_readings' =>
                fn(): DashboardCardInterface => new ElectricityMeterReadingsCard($access_points),
            'radar_interferences' => fn(): DashboardCardInterface => new RadarInterferencesCard($interferences),
            'power_outages' => fn(): DashboardCardInterface => new PowerOutagesCard($power_outages),
        ];
    }

    /**
     * The same four cards, asked of the other application instead of of this one.
     *
     * @param \App\CRM\Tasks $tasks The tasks of the other application.
     * @param string|null $username The signed-in operator, by the shared name.
     * @return array<string, callable(): \Dashboard\Card\DashboardCardInterface>
     */
    private function crmTaskCards(CrmTasks $tasks, ?string $username): array
    {
        return [
            'pressing_tasks' => fn(): DashboardCardInterface => new CrmPressingTasksCard($tasks),
            'my_tasks' => fn(): DashboardCardInterface => new CrmMyTasksCard($tasks, $username),
            'unassigned_tasks' => fn(): DashboardCardInterface => new CrmUnassignedTasksCard($tasks),
            'stale_tasks' => fn(): DashboardCardInterface => new CrmStaleTasksCard($tasks),
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
