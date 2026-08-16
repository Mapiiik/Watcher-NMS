<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\DashboardController;
use App\Test\Traits\ControllerTestTrait;
use Cake\Core\Configure;
use Cake\Routing\Router;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Controller\DashboardController Test Case
 */
#[UsesClass(DashboardController::class)]
class DashboardControllerTest extends TestCase
{
    use ControllerTestTrait;
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.AppUsers',
        'app.AccessPointTypes',
        'app.AccessPoints',
        'app.ElectricityMeterReadings',
        'app.CustomerPoints',
        'app.CustomerConnections',
        'app.DeviceTypes',
        'app.RouterosDevices',
        'app.RouterosDeviceInterfaces',
        'app.RadarInterferences',
        'app.TaskStates',
        'app.TaskTypes',
        'app.Tasks',
    ];

    /**
     * The dashboard is the landing page, so the root has to render it rather than redirect.
     *
     * @return void
     * @link \App\Controller\DashboardController::index()
     */
    public function testRootRendersTheDashboard(): void
    {
        $this->login();
        $this->get('/');

        $this->assertResponseOk();
        $this->assertNotEmpty($this->viewVariable('cards'));
    }

    /**
     * Signing in has to land on the dashboard's own route rather than on the fallback
     * `/dashboard`. A plugin's assets are linked into the webroot under the plugin's own
     * name, and this plugin is named after the page it draws - so that path is a directory
     * the web server answers itself, before the router is ever asked.
     *
     * @return void
     */
    public function testTheLoginLandsOnTheDashboardsOwnRoute(): void
    {
        // the plugin that owns this setting merges it in as the application boots, which a
        // request is what brings about
        $this->login();
        $this->get('/');
        $this->assertResponseOk();

        $redirect = (string)Configure::read('Auth.AuthenticationComponent.loginRedirect');

        $this->assertSame(
            Router::url(['controller' => 'Dashboard', 'action' => 'index', 'plugin' => null]),
            $redirect,
        );

        $this->get($redirect);

        $this->assertResponseOk();
        $this->assertNotEmpty($this->viewVariable('cards'));
    }

    /**
     * The cards sit on the panel every other index page uses, drawn as the grouped blocks
     * elsewhere are.
     *
     * @return void
     * @link \App\Controller\DashboardController::index()
     */
    public function testIndex(): void
    {
        $this->login();
        $this->get('/dashboard');

        $this->assertResponseOk();
        $this->assertResponseContains('class="dashboard index content"');
        $this->assertResponseContains('class="dashboard-cards"');
        $this->assertResponseContains('class="related"');
        // served out of the plugin, so the path says which one it came from
        $this->assertResponseContains('/dashboard/css/dashboard.css');
        // the deferred cards are the only thing that fetches itself, so the script comes with
        // this page rather than with every page
        $this->assertResponseContains('js/lazy-load.js');
    }

    /**
     * A page with no deferred card does not drag the script that fetches them along.
     *
     * @return void
     * @link \App\Controller\DashboardController::index()
     */
    public function testTheLazyLoadScriptIsNotOnEveryPage(): void
    {
        $this->login();
        $this->get('/tasks');

        $this->assertResponseOk();
        $this->assertResponseNotContains('js/lazy-load.js');
    }

    /**
     * Every role has to get past the landing page, whatever it is then shown there. A role
     * that 403s here cannot sign in and reach anything at all.
     *
     * @param string $role The role to sign in as.
     * @return void
     * @link \App\Controller\DashboardController::index()
     */
    #[DataProvider('roleProvider')]
    public function testEveryRoleReachesTheDashboard(string $role): void
    {
        $this->login($role);
        $this->get('/dashboard');

        $this->assertResponseOk();
    }

    /**
     * @return list<array{string}>
     */
    public static function roleProvider(): array
    {
        return [
            ['user'],
            ['customer-service-technician'],
            ['network-technician'],
            ['network-manager'],
            ['sales-representative'],
            ['sales-manager'],
            ['bookkeeper'],
            ['admin'],
        ];
    }

    /**
     * A technician is not offered the cards meant for managers.
     *
     * @return void
     * @link \App\Dashboard\DashboardCardRegistry::forRole()
     */
    public function testCardsAreChosenByRole(): void
    {
        $this->login('customer-service-technician');
        $this->get('/dashboard');

        $this->assertResponseOk();

        /** @var list<\Dashboard\Card\DashboardCardInterface> $cards */
        $cards = $this->viewVariable('cards');
        $ids = array_map(fn($card): string => $card->id(), $cards);

        $this->assertContains('my_tasks', $ids);
        $this->assertNotContains('unassigned_tasks', $ids);
    }

    /**
     * Each card answers with the bare fragment when fetched on its own.
     *
     * @param string $id The card to fetch.
     * @return void
     * @link \App\Controller\DashboardController::card()
     */
    #[DataProvider('cardProvider')]
    public function testCard(string $id): void
    {
        $this->login();
        $this->get('/dashboard/card/' . $id);

        $this->assertResponseOk();
        $this->assertResponseNotContains('<html');
    }

    /**
     * @return list<array{string}>
     */
    public static function cardProvider(): array
    {
        return [
            ['pressing_tasks'],
            ['my_tasks'],
            ['unassigned_tasks'],
            ['stale_tasks'],
            ['stale_device_data'],
            ['electricity_meter_readings'],
            ['radar_interferences'],
        ];
    }

    /**
     * The register lists everybody's interferences; the card draws only the ones joined to
     * a device of ours, so a matched device has to actually come out of it.
     *
     * @return void
     * @link \App\Dashboard\Card\RadarInterferencesCard::data()
     */
    public function testRadarInterferencesAreMatchedToOurDevices(): void
    {
        $this->login();
        $this->get('/dashboard/card/radar_interferences');

        $this->assertResponseOk();
        // the fixtures share a MAC between an interference and a device interface
        $this->assertResponseContains('/routeros-devices/view/');
    }

    /**
     * The link under a card has to reproduce that card's set, so it names every filter the
     * listing keeps in the session rather than leaving the last one used in force.
     *
     * @return void
     * @link \App\Dashboard\Card\AbstractTaskListCard::listingUrl()
     */
    public function testTaskCardsLinkToTheirOwnSet(): void
    {
        $this->login();
        $this->get('/dashboard/card/pressing_tasks');

        $this->assertResponseOk();

        /** @var \Dashboard\Card\DashboardCardInterface $card */
        $card = $this->viewVariable('card');
        $query = $card->data()['url']['?'];

        $this->assertSame(1, $query['pressing']);
        $this->assertSame(0, $query['stale']);
        $this->assertSame(0, $query['show_completed']);
        // a filter the operator last used must not still be narrowing the listing
        $this->assertSame('', $query['user_id']);
        $this->assertSame('', $query['task_state_id']);
        $this->assertSame('', $query['search']);
    }

    /**
     * The listing understands the filters those links carry, and narrows by them.
     *
     * @return void
     * @link \App\Controller\TasksController::index()
     */
    public function testTheListingUnderstandsTheCardFilters(): void
    {
        $this->login();
        $this->get('/tasks?pressing=1&stale=0&show_completed=0&user_id=');

        $this->assertResponseOk();

        $this->get('/tasks?pressing=0&stale=1&show_completed=0&user_id=');

        $this->assertResponseOk();
    }

    /**
     * The second line of a task row is the summary without the subject, which the heading
     * above it already carries. Reading it needs the contract and an address per customer
     * while the rows are ordered by columns of the task, and left to the `subquery` strategy
     * PostgreSQL refuses that - so this asks with a task actually in hand, which is the only
     * way the eager loading runs at all.
     *
     * @return void
     * @link \App\Model\Entity\Task::getSummaryText()
     */
    public function testATaskRowCarriesItsSummary(): void
    {
        $states = $this->getTableLocator()->get('TaskStates');
        $open = $states->find()->where(['completed' => false])->first();
        if ($open === null) {
            $open = $states->saveOrFail($states->newEntity([
                'name' => 'Open',
                'color' => '#ffffff',
                'completed' => false,
                'priority' => 1,
            ]));
        }

        // the fixtures write the identity column with the values they carry, which leaves the
        // sequence where it started
        $this->advanceIdentity('Tasks', 'nid');

        $tasks = $this->getTableLocator()->get('Tasks');
        $tasks->saveOrFail($tasks->newEntity([
            'task_type_id' => $this->firstId('TaskTypes'),
            'task_state_id' => $open->get('id'),
            'subject' => 'A subject of its own',
            'priority' => 0,
            // without one the summary has nothing left to say once the subject is dropped
            'access_point_id' => $this->firstId('AccessPoints'),
        ]));

        $this->login();
        $this->get('/dashboard/card/unassigned_tasks');

        $this->assertResponseOk();
        // the heading carries the subject, so the line below it must not repeat it
        $this->assertResponseContains('A subject of its own');
        $this->assertResponseContains('class="dashboard-hint"');
    }

    /**
     * A card nobody registered is not a page.
     *
     * @return void
     * @link \App\Controller\DashboardController::card()
     */
    public function testCardThatDoesNotExist(): void
    {
        $this->login();
        $this->get('/dashboard/card/not_a_card');

        $this->assertResponseCode(404);
    }

    /**
     * A card the signed-in role is not offered is not reachable by asking for it directly,
     * as the permissions only guard the action rather than the individual cards.
     *
     * @return void
     * @link \App\Controller\DashboardController::card()
     */
    public function testCardTheRoleIsNotOffered(): void
    {
        $this->login('customer-service-technician');
        $this->get('/dashboard/card/unassigned_tasks');

        $this->assertResponseCode(404);
    }
}
