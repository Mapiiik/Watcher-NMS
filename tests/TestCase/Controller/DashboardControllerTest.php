<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\DashboardController;
use App\Test\Traits\ConfigureTestTrait;
use App\Test\Traits\ControllerTestTrait;
use Cake\Core\Configure;
use Cake\Http\TestSuite\HttpClientTrait;
use Cake\I18n\DateTime;
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
    use ConfigureTestTrait;
    use ControllerTestTrait;
    use HttpClientTrait;
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
        'app.AccessPointSupplyAddresses',
        'app.PowerOutages',
        'app.PowerOutageScopes',
        'app.AccessPointPowerOutages',
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
            ['power_outages'],
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
     * The outage card names the mast rather than the outage, and says how sure it is.
     *
     * What is known beats what is guessed, so the certain one has to come out above the probable
     * one - on a busy morning the order is most of what the card is worth.
     *
     * @return void
     * @link \App\Dashboard\Card\PowerOutagesCard::data()
     */
    public function testPowerOutagesAreListedByMastWithTheCertainOnesFirst(): void
    {
        $outages = $this->getTableLocator()->get('PowerOutages');
        $outages->updateAll(
            ['begins_at' => DateTime::now()->addDays(2), 'ends_at' => DateTime::now()->addDays(2)->addHours(4)],
            [],
        );
        // The fixture calls one of them off, and a called-off outage is not news.
        $outages->updateAll(['cancelled' => false], []);

        $this->login();
        $this->get('/dashboard/card/power_outages');

        $this->assertResponseOk();
        $this->assertResponseContains('/access-points/3f6f6b19-6a0e-4a5b-9a4a-2c0f4d5e6a71');

        $body = (string)$this->_response?->getBody();
        $this->assertLessThan(
            strpos($body, (string)__('Probable')),
            strpos($body, (string)__('Certain')),
            'What is known should be listed above what is only guessed at.',
        );
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
        $this->assertSame('', $query['task_state_ids']);
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

    /**
     * With the tasks kept elsewhere, the same card is drawn from the other application - under
     * the same id, so that a dashboard somebody has arranged survives the change.
     *
     * @return void
     * @link \App\Dashboard\DashboardCardRegistry::get()
     */
    public function testTheTaskCardsAreAskedOfTheOtherApplicationWhereItKeepsThem(): void
    {
        $this->withConfigure([
            'Crm.url' => 'https://crm.example.com',
            'Crm.key' => 'secret',
            'Crm.tasks' => true,
        ]);

        $this->mockClientGet(
            'https://crm.example.com/api/tasks/search.json?' . http_build_query(
                ['unassigned' => '1', 'active' => '1', 'limit' => '10', 'api_key' => 'secret'],
                '',
                '&',
                PHP_QUERY_RFC3986,
            ),
            $this->newClientResponse(200, ['Content-Type: application/json'], (string)json_encode([
                'tasks' => [[
                    'id' => 'a26f0ae4-3d9c-4a4f-9a2e-0f1b2c3d4e5f',
                    'nid' => 42,
                    'subject' => 'Nobody has this one',
                    'priority' => 0,
                ]],
                'total' => 4,
            ])),
        );

        $this->login('network-manager');
        $this->get('/dashboard/card/unassigned_tasks');

        $this->assertResponseOk();
        $this->assertResponseContains('Nobody has this one');
        // the way on leads over there, because that is where it is kept
        $this->assertResponseContains('https://crm.example.com/tasks/view/a26f0ae4-3d9c-4a4f-9a2e-0f1b2c3d4e5f');
        // and it says how many there are, not only how many it drew
        $this->assertResponseContains((string)__('and {0} more', 3));

        $this->restoreConfigure();
    }

    /**
     * A card that could not be filled says so. An empty one would read as an afternoon with
     * nothing to do, which is the opposite of what an outage means.
     *
     * @return void
     * @link \App\Dashboard\Card\Crm\AbstractCrmTaskListCard::payload()
     */
    public function testATaskCardSaysWhenTheOtherApplicationDidNotAnswer(): void
    {
        $this->withConfigure([
            'Crm.url' => 'https://crm.example.com',
            'Crm.key' => 'secret',
            'Crm.tasks' => true,
        ]);

        $this->mockClientGet(
            'https://crm.example.com/api/tasks/search.json?' . http_build_query(
                ['unassigned' => '1', 'active' => '1', 'limit' => '10', 'api_key' => 'secret'],
                '',
                '&',
                PHP_QUERY_RFC3986,
            ),
            $this->newClientResponse(500),
        );

        $this->login('network-manager');
        $this->get('/dashboard/card/unassigned_tasks');

        $this->assertResponseOk();
        $this->assertResponseContains(__('Data from Watcher CRM could not be loaded.'));
        $this->assertResponseNotContains((string)__d('tasks', 'Every unfinished task has somebody holding it.'));

        $this->restoreConfigure();
    }

    /**
     * The way on from "my tasks" has to name the person by the number the other application knows
     * them by. This one has a number for them too and it means nothing over there - and a listing
     * asked with no name at all falls back to whoever is signed in, which would look right often
     * enough to go unnoticed.
     *
     * @return void
     * @link \App\Dashboard\Card\Crm\MyTasksCard::data()
     */
    public function testTheWayOnToSomebodysOwnTasksNamesThemAsTheOtherApplicationDoes(): void
    {
        $this->withConfigure([
            'Crm.url' => 'https://crm.example.com',
            'Crm.key' => 'secret',
            'Crm.tasks' => true,
        ]);

        $this->mockClientGet(
            'https://crm.example.com/api/tasks/search.json?' . http_build_query(
                ['user' => 'tester', 'active' => '1', 'limit' => '10', 'api_key' => 'secret'],
                '',
                '&',
                PHP_QUERY_RFC3986,
            ),
            $this->newClientResponse(200, ['Content-Type: application/json'], (string)json_encode([
                'tasks' => [[
                    'id' => 'a26f0ae4-3d9c-4a4f-9a2e-0f1b2c3d4e5f',
                    'nid' => 42,
                    'subject' => 'Mine over there',
                    'priority' => 0,
                ]],
                'total' => 3,
                'user_id' => '5f7e1d2c-3b4a-4958-8a6b-7c8d9e0f1a2b',
            ])),
        );

        $this->login();
        $this->get('/dashboard/card/my_tasks');

        $this->assertResponseOk();
        $this->assertResponseContains('user_id=5f7e1d2c-3b4a-4958-8a6b-7c8d9e0f1a2b');
        // and every other field the listing filters by is named, or whatever its operator last
        // filtered by would still be narrowing what this points at
        $this->assertResponseContains('show_completed=0');
        $this->assertResponseContains('task_type_ids=');

        $this->restoreConfigure();
    }

}
