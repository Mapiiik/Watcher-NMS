<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\AccessPointsController;
use App\Test\Traits\ConfigureTestTrait;
use App\Test\Traits\ControllerTestTrait;
use App\Test\Traits\IdentityColumnTrait;
use Cake\Cache\Cache;
use Cake\Http\TestSuite\HttpClientTrait;
use Cake\I18n\DateTime;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Maps\Geocoder\AddressRegistryGeocoder;
use Override;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Controller\AccessPointsController Test Case
 */
#[UsesClass(AccessPointsController::class)]
class AccessPointsControllerTest extends TestCase
{
    use ConfigureTestTrait;
    use ControllerTestTrait;
    use HttpClientTrait;
    use IdentityColumnTrait;
    use IntegrationTestTrait;

    /**
     * The access point the fixtures leave in use.
     *
     * @var string
     */
    private const ACTIVE_ACCESS_POINT_ID = '1bd5e754-e102-46ad-8488-11b1b44bf026';

    /**
     * The access point the fixtures have already put away.
     *
     * @var string
     */
    private const ARCHIVED_ACCESS_POINT_ID = '1ec58677-1213-4950-80c4-bc1de41ea133';

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.AppUsers',
        'app.AccessPointTypes',
        'app.AccessPoints',
        'app.AccessPointContacts',
        'app.AccessPointSupplyAddresses',
        'app.PowerOutages',
        'app.PowerOutageScopes',
        'app.AccessPointPowerOutages',
        'app.Manufacturers',
        'app.PowerSupplyTypes',
        'app.PowerSupplies',
        'app.RadioLinks',
        'app.RadioUnitBands',
        'app.RadioUnitTypes',
        'app.AntennaTypes',
        'app.RadioUnits',
        'app.CustomerPoints',
        'app.CustomerConnections',
        'app.DeviceTypes',
        'app.RouterosDevices',
        'app.RouterosDeviceIps',
        'app.RouterosDeviceInterfaces',
        'app.TaskStates',
        'app.TaskTypes',
        'app.Tasks',
    ];

    /**
     * tearDown method
     *
     * @return void
     */
    #[Override]
    protected function tearDown(): void
    {
        $this->restoreConfigure();

        parent::tearDown();
    }

    /**
     * The listing renders, plain and with the search filled in - the search builds a different
     * query than the plain listing does.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::index()
     */
    public function testIndex(): void
    {
        $this->login();
        $this->get('/access-points');

        $this->assertResponseOk();

        $this->get('/access-points?search=Lorem');

        $this->assertResponseOk();
    }

    /**
     * The detail says what is planned for the power, and on what grounds.
     *
     * The grounds matter as much as the verdict: an outage found through the addresses around a
     * mast is a guess, and the page has to let the operator see what it was guessed from.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::view()
     */
    public function testViewShowsThePlannedOutagesAndWhatTheyRestOn(): void
    {
        $this->login();
        $this->get('/access-points/view/3f6f6b19-6a0e-4a5b-9a4a-2c0f4d5e6a71');

        $this->assertResponseOk();
        $this->assertResponseContains(__('Planned Power Outages'));

        // The one found through the supply point, and the one only guessed at from an address.
        $this->assertResponseContains(__('Certain'));
        $this->assertResponseContains(__('Probable'));
        $this->assertResponseContains('Hlubocska 106 (42 m)');

        // The supply point is shown. Whether the fault check is offered depends on configuration,
        // so that is asked about on its own below rather than leaned on here.
        $this->assertResponseContains('859182400000001231');
    }

    /**
     * The fault check arrives with the place already in it.
     *
     * The number is the one the address registry keeps the nearest address under, which is what
     * the outage widget on the distributor page starts from - so the operator does not have to
     * type the address again. Undocumented, hence the address is written out beside the link too.
     *
     * The configuration is said out loud rather than left to the environment: the machine this
     * runs on may have turned outages on in its own `.env`, and a test passing for that reason
     * would fail on one that has not.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::view()
     */
    public function testTheFaultCheckCarriesThePlaceItAlreadyKnows(): void
    {
        $this->withConfigure([
            'PowerOutages.enabled' => true,
            'PowerOutages.faultsUrl' => 'https://distributor.example.com/no-power',
            'PowerOutages.plannedUrl' => 'https://distributor.example.com/planned',
            'Maps.geocoder' => AddressRegistryGeocoder::class,
            'Maps.addressRegistry.url' => 'https://addresses.example.com',
            'Maps.addressRegistry.key' => '',
            'Maps.addressRegistry.defaultCountries' => 'cz',
        ]);
        Cache::clear();
        $this->mockNearestAddress('cz', '21154996');

        $this->login();
        $this->get('/access-points/view/3f6f6b19-6a0e-4a5b-9a4a-2c0f4d5e6a71');

        $this->assertResponseOk();
        // Both questions the distributor answers, each carrying the same place.
        $this->assertResponseContains('https://distributor.example.com/no-power?jlAddress=21154996');
        $this->assertResponseContains('https://distributor.example.com/planned?jlAddress=21154996');
        // The wording of the address is not repeated beside them; it stands in the row above, and
        // this is what says all of it comes out of one lookup rather than several.
        $this->assertResponseContains('Karlovo namesti 91, 28002 Kolin');
    }

    /**
     * An installation that does not read outages does not offer the link at all.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::view()
     */
    public function testTheFaultCheckIsNotOfferedWhenOutagesAreOff(): void
    {
        $this->withConfigure(['PowerOutages.enabled' => false]);
        $this->login();

        $this->get('/access-points/view/3f6f6b19-6a0e-4a5b-9a4a-2c0f4d5e6a71');

        $this->assertResponseOk();
        $this->assertResponseNotContains(__('Check for a Fault'));
        $this->assertResponseNotContains(__('Check for a Planned Outage'));
    }

    /**
     * What the address registry answers about the mast in the fixture.
     *
     * @param string $source Which country register the record came out of.
     * @param string $registryRef What that register keeps it under.
     * @return void
     */
    private function mockNearestAddress(string $source, string $registryRef): void
    {
        $this->mockClientGet(
            'https://addresses.example.com/v1/reverse?' . http_build_query([
                'country' => 'cz',
                'lat' => 50.0281552,
                'lon' => 15.200344,
                'limit' => 1,
            ]),
            $this->newClientResponse(200, ['Content-Type: application/json'], (string)json_encode([[
                'formatted_address' => 'Karlovo namesti 91, 28002 Kolin',
                'geometry' => ['type' => 'Point', 'coordinates' => [15.200344, 50.0281552]],
                'source' => $source,
                'registry_ref' => $registryRef,
            ]])),
        );
    }

    /**
     * A mast with nothing to go on says so rather than showing an empty list.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::view()
     */
    public function testViewSaysWhenThereIsNothingToGoOn(): void
    {
        $accessPoints = $this->getTableLocator()->get('AccessPoints');
        $alone = $accessPoints->saveOrFail($accessPoints->newEntity(['name' => 'Mast in a field']));

        $this->login();
        $this->get('/access-points/view/' . $alone->get('id'));

        $this->assertResponseOk();
        $this->assertResponseContains(__('No address was found near this access point,'
            . ' so only a supply point can reveal an outage.'));
    }

    /**
     * How the outages were looked for is described as they were actually looked for.
     *
     * A mast with its supply point written down is asked about directly, so saying it was looked
     * for around the addresses near it would describe a search that did not happen - and it is the
     * sort of wording that quietly stops being true when the behaviour behind it moves.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::view()
     */
    public function testViewDescribesHowTheOutagesWereActuallyLookedFor(): void
    {
        $accessPoints = $this->getTableLocator()->get('AccessPoints');
        $withEan = $accessPoints->get('3f6f6b19-6a0e-4a5b-9a4a-2c0f4d5e6a71');

        $this->login();
        $this->get('/access-points/view/' . $withEan->get('id'));

        $this->assertResponseOk();
        $this->assertResponseContains(
            __('The distributor is asked about the supply point, so what it names is this access point.'),
        );
        // The addresses are still compared, so that is said rather than implied - but not as the
        // way the outages were looked for.
        $this->assertResponseNotContains(__('Looked for around the {0} nearest addresses to the access point.', 2));
        $this->assertResponseNotContains(
            __('Without the EAN of the supply point the outages are only ever probable.'),
        );

        // The same mast once nobody has written its supply point down.
        $withEan->set('electricity_ean', null);
        $accessPoints->saveOrFail($withEan);

        $this->get('/access-points/view/' . $withEan->get('id'));

        $this->assertResponseContains(__('Looked for around the {0} nearest addresses to the access point.', 2));
        $this->assertResponseContains(
            __('Without the EAN of the supply point the outages are only ever probable.'),
        );
    }

    /**
     * Test view method
     *
     * @return void
     */
    public function testView(): void
    {
        $this->login();

        $accessPoints = $this->getTableLocator()->get('AccessPoints');
        $root = $accessPoints->saveOrFail($accessPoints->newEntity(['name' => 'Tree root']));
        $child = $accessPoints->saveOrFail($accessPoints->newEntity([
            'name' => 'Tree child',
            'parent_access_point_id' => $root->id,
        ]));

        $this->get('/access-points/view/' . $root->id);

        $this->assertResponseOk();
        // The heading is translated, so it is looked up rather than hard coded.
        $this->assertResponseContains(__('Subordinate Access Points'));
        // Every section is reachable from the side of the page, the last of them included.
        $this->assertResponseContains(__('Sections'));
        $this->assertResponseContains('/access-points/' . $root->id . '#tasks');
        $this->assertResponseContains('<h4 id="tasks">');
        $this->assertResponseContains('<a href="/access-points/' . $child->id . '">Tree child</a>');
        // Its own map is offered, nested under it and with every layer asked for.
        $this->assertResponseContains('/access-points/' . $root->id . '/map?');

        $this->get('/access-points/view/' . $child->id);

        $this->assertResponseOk();
        // The path leads from the root down to the access point itself.
        $this->assertResponseContains('Tree root</a> &gt; Tree child');
    }

    /**
     * The radio links of an access point say where their far end stands, as the links read off the
     * devices do - a link is only worth listing if it says what is at the other end of it.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::view()
     */

    /**
     * The tasks of a place are listed on its card - all of them, with the unfinished above the
     * rest.
     *
     * A mast gathers tasks for as long as it stands and the finished ones are the bulk of them,
     * so hiding them would lose the history; ordering is what keeps the list useful instead.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::view()
     */
    public function testViewListsEveryTaskOfThePlaceUnfinishedFirst(): void
    {
        $accessPoints = $this->getTableLocator()->get('AccessPoints');
        $place = $accessPoints->saveOrFail($accessPoints->newEntity(['name' => 'Mast with a history']));

        // The fixtures carry one state and it is an unfinished one, so the finished one this
        // test needs is made here.
        $taskStates = $this->getTableLocator()->get('TaskStates');
        $unfinished = $taskStates->find()->firstOrFail();
        $finished = $taskStates->saveOrFail($taskStates->newEntity([
            'name' => 'Seen to',
            'color' => '#f4f4f4',
            'priority' => -50,
            'completed' => true,
        ]));

        $taskType = $this->getTableLocator()->get('TaskTypes')->find()->firstOrFail();
        $tasks = $this->getTableLocator()->get('Tasks');

        // the fixtures write the identity column with the values they carry, which leaves the
        // identity itself where it started
        $this->advanceIdentity('Tasks', 'nid');

        // Written finished first on purpose: were the list to follow the order they were made
        // in, this test would pass for the wrong reason.
        foreach ([$finished->get('id') => 'Long since seen to', $unfinished->get('id') => 'Still to do'] as $state => $subject) {
            $tasks->saveOrFail($tasks->newEntity([
                'task_state_id' => $state,
                'task_type_id' => $taskType->get('id'),
                'access_point_id' => $place->get('id'),
                'subject' => $subject,
                'priority' => 0,
            ]));
        }

        $this->login();
        $this->get('/access-points/view/' . $place->get('id'));

        $this->assertResponseOk();
        // The heading is translated, so it is looked up rather than hard coded.
        $this->assertResponseContains(__('Related Tasks'));

        // Both are there - finishing a task does not take it off the mast it was done at.
        $this->assertResponseContains('Still to do');
        $this->assertResponseContains('Long since seen to');

        $body = (string)$this->_response?->getBody();
        $this->assertLessThan(
            strpos($body, 'Long since seen to'),
            strpos($body, 'Still to do'),
            'What is still to be done should be listed above what has been.',
        );
    }

    /**
     * With the tasks kept elsewhere, the section is still there - fed over the bridge, with the
     * way on to the other application instead of the actions that would write here.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::view()
     */
    public function testViewListsTheTasksTheOtherApplicationKeeps(): void
    {
        $accessPoints = $this->getTableLocator()->get('AccessPoints');
        $place = $accessPoints->saveOrFail($accessPoints->newEntity(['name' => 'Mast kept elsewhere']));

        $this->withConfigure([
            'Crm.url' => 'https://crm.example.com',
            'Crm.key' => 'secret',
            'Crm.tasks' => true,
        ]);

        $this->mockClientGet(
            'https://crm.example.com/api/tasks/search.json?' . http_build_query(
                ['access_point_id' => $place->get('id'), 'api_key' => 'secret'],
                '',
                '&',
                PHP_QUERY_RFC3986,
            ),
            $this->newClientResponse(200, ['Content-Type: application/json'], (string)json_encode([
                'tasks' => [[
                    'id' => 'a26f0ae4-3d9c-4a4f-9a2e-0f1b2c3d4e5f',
                    'nid' => 42,
                    'subject' => 'Kept over there',
                    'priority' => 0,
                    'access_point_id' => $place->get('id'),
                ]],
                'total' => 1,
            ])),
        );

        $this->login();
        $this->get('/access-points/view/' . $place->get('id'));

        $this->assertResponseOk();
        $this->assertResponseContains(__('Related Tasks'));
        $this->assertResponseContains('Kept over there');

        // the way on to where it is actually kept, and nothing that would write to it from here
        $this->assertResponseContains('https://crm.example.com/tasks/view/a26f0ae4-3d9c-4a4f-9a2e-0f1b2c3d4e5f');
        $this->assertResponseNotContains(__('New Task'));
    }

    /**
     * A reading that never arrived says so. An empty section would read as a mast with nothing to
     * be done at it, which is the one thing it must not be mistaken for.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::view()
     */
    public function testViewSaysWhenTheOtherApplicationDidNotAnswer(): void
    {
        $accessPoints = $this->getTableLocator()->get('AccessPoints');
        $place = $accessPoints->saveOrFail($accessPoints->newEntity(['name' => 'Mast nobody answered for']));

        $this->withConfigure([
            'Crm.url' => 'https://crm.example.com',
            'Crm.key' => 'secret',
            'Crm.tasks' => true,
        ]);

        $this->mockClientGet(
            'https://crm.example.com/api/tasks/search.json?' . http_build_query(
                ['access_point_id' => $place->get('id'), 'api_key' => 'secret'],
                '',
                '&',
                PHP_QUERY_RFC3986,
            ),
            $this->newClientResponse(500),
        );

        $this->login();
        $this->get('/access-points/view/' . $place->get('id'));

        $this->assertResponseOk();
        $this->assertResponseContains(__('Data from Watcher CRM could not be loaded.'));
    }

    public function testViewListsTheFarEndOfEveryRadioLink(): void
    {
        $map = $this->createMapTopology();

        $this->login();
        $this->get('/access-points/view/' . $map['home_access_point_id']);

        $this->assertResponseOk();
        $this->assertResponseContains(__('Related Radio Unit Links'));
        // the end at the neighbour, named along with the mast it stands on
        $this->assertResponseContains('Map backhaul far end');
        $this->assertResponseContains(
            '<a href="/access-points/' . $map['neighbouring_access_point_id'] . '">Map neighbour</a>',
        );
        // and the end at a customer, named along with the connection it hangs off
        $this->assertResponseContains('Map customer link far end');
        $this->assertResponseContains('Map radio customer connection');
    }

    /**
     * Test utilization method
     *
     * @return void
     */
    public function testUtilization(): void
    {
        $this->login();

        $accessPoints = $this->getTableLocator()->get('AccessPoints');
        $root = $accessPoints->saveOrFail($accessPoints->newEntity(['name' => 'Tree root']));
        $child = $accessPoints->saveOrFail($accessPoints->newEntity([
            'name' => 'Tree child',
            'parent_access_point_id' => $root->id,
        ]));

        $this->get('/access-points/utilization');

        $this->assertResponseOk();
        // The heading is translated, so it is looked up rather than hard coded.
        $this->assertResponseContains(__('Access Points Utilization'));
        // Unlike the subtree of a single access point, the roots are listed as links as well.
        $this->assertResponseContains('<a href="/access-points/' . $root->id . '">Tree root</a>');
        $this->assertResponseContains('<a href="/access-points/' . $child->id . '">Tree child</a>');
    }

    /**
     * Test utilization method with a filter
     *
     * @return void
     */
    public function testUtilizationFilter(): void
    {
        $this->login();

        $accessPoints = $this->getTableLocator()->get('AccessPoints');
        $root = $accessPoints->saveOrFail($accessPoints->newEntity(['name' => 'Tree root']));
        $child = $accessPoints->saveOrFail($accessPoints->newEntity([
            'name' => 'Tree child',
            'parent_access_point_id' => $root->id,
        ]));
        $empty = $accessPoints->saveOrFail($accessPoints->newEntity(['name' => 'Empty root']));

        $customerConnections = $accessPoints->CustomerConnections;
        $customerConnections->saveOrFail($customerConnections->newEntity([
            'name' => 'Customer connection',
            'access_point_id' => $child->id,
        ]));

        $this->get('/access-points/utilization?min_customer_connections=1');

        $this->assertResponseOk();
        $this->assertResponseContains('<a href="/access-points/' . $child->id . '">Tree child</a>');
        // The root carries nothing of its own, it is listed as the path down to the child.
        $this->assertResponseContains('<a href="/access-points/' . $root->id . '">Tree root</a>');
        $this->assertResponseNotContains('<a href="/access-points/' . $empty->id . '">Empty root</a>');
    }

    /**
     * The form for a new access point renders.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::add()
     */
    public function testAdd(): void
    {
        $this->login();
        $this->get('/access-points/add');

        $this->assertResponseOk();
    }

    /**
     * The form of an existing access point renders.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::edit()
     */
    public function testEdit(): void
    {
        $this->login();
        $this->get('/access-points/' . $this->firstId('AccessPoints') . '/edit');

        $this->assertResponseOk();
    }

    /**
     * An access point filled in on the form is really stored.
     *
     * Rendering the form proves the page is there; this proves the way through it works. Everything
     * between the two - marshalling, validation, the application rules and the save - only ever
     * runs on a request that carries data, and a controller test that never posts one leaves the
     * whole of it unasked.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::add()
     */
    public function testAddStoresAnAccessPoint(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/access-points/add', [
            'name' => 'Hilltop relay',
            'access_point_type_id' => $this->firstId('AccessPointTypes'),
        ]);

        $this->assertRedirect();
        /** @var \App\Model\Entity\AccessPoint $stored */
        $stored = $this->getTableLocator()->get('AccessPoints')
            ->find()
            ->where(['name' => 'Hilltop relay'])
            ->firstOrFail();
        $this->assertSame($this->firstId('AccessPointTypes'), $stored->access_point_type_id);
    }

    /**
     * An access point of a type that is not there is not stored, and the operator is given the form
     * back rather than a redirect that would suggest it went through.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::add()
     */
    public function testAddRefusesAnAccessPointOfATypeThatIsNotThere(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $accessPoints = $this->getTableLocator()->get('AccessPoints');
        $before = $accessPoints->find()->count();

        $this->post('/access-points/add', [
            'name' => 'Hilltop relay',
            'access_point_type_id' => '3f2b1a0c-0000-4000-8000-000000000000',
        ]);

        $this->assertResponseOk();
        $this->assertSame($before, $accessPoints->find()->count());
    }

    /**
     * A change made on the form reaches the record.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::edit()
     */
    public function testEditStoresTheChange(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $accessPointId = $this->firstId('AccessPoints');
        $this->post('/access-points/' . $accessPointId . '/edit', ['name' => 'Renamed relay']);

        $this->assertRedirect();
        $this->assertSame(
            'Renamed relay',
            $this->getTableLocator()->get('AccessPoints')->get($accessPointId)->name,
        );
    }

    /**
     * A role that is not admin does not get to add an access point.
     *
     * Every other test here logs in as admin, which `config/permissions.php` lets through
     * everything - so none of them can tell a controller that is guarded from one that is not. This
     * asks the authorization layer the only question that matters about it: does a refusal really
     * happen.
     *
     * A refusal is a redirect away rather than a status in the 400s - the middleware sends whoever
     * is not allowed somewhere they are - so what this holds on to is that they do not arrive at
     * the form.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::add()
     */
    public function testAddIsRefusedToANonAdminRole(): void
    {
        $this->login('api');

        $this->get('/access-points/add');

        $this->assertRedirect('/');
    }

    /**
     * The same role does get to list access points, which every role is allowed. Without this the
     * test above would pass just as well on a role that is refused everything, and would be saying
     * nothing about the permissions at all.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::index()
     */
    public function testIndexIsAllowedToANonAdminRole(): void
    {
        $this->login('api');

        $this->get('/access-points');

        $this->assertResponseOk();
    }

    /**
     * An access point nothing hangs off is deleted and the caller is sent back to the listing.
     *
     * A fresh one is made for it rather than taking a fixture: deleting an access point that still
     * parents another reaches the foreign key and answers 500, which is worth knowing but is not
     * what this test is about.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::delete()
     */
    public function testDelete(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $accessPoints = $this->getTableLocator()->get('AccessPoints');
        $accessPoint = $accessPoints->saveOrFail($accessPoints->newEntity(['name' => 'Leaf to delete']));

        $this->post('/access-points/' . $accessPoint->id . '/delete');

        $this->assertRedirect();
        $this->assertFalse($accessPoints->exists(['id' => $accessPoint->id]));
    }

    /**
     * The map renders.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::map()
     */
    public function testMap(): void
    {
        $this->login();
        $this->get('/access-points/map');

        $this->assertResponseOk();
    }

    /**
     * An access point that is no longer in use is put away rather than taken away: it is marked as
     * archived, and it is still there afterwards.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::archive()
     */
    public function testArchivePutsTheAccessPointAwayWithoutTakingItAway(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $accessPoints = $this->getTableLocator()->get('AccessPoints');

        $this->post('/access-points/archive/' . self::ACTIVE_ACCESS_POINT_ID);

        $this->assertRedirect();
        $this->assertNotNull($accessPoints->get(self::ACTIVE_ACCESS_POINT_ID)->get('archived'));
    }

    /**
     * An access point that was put away is brought back into use.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::restore()
     */
    public function testRestoreBringsTheAccessPointBackIntoUse(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $accessPoints = $this->getTableLocator()->get('AccessPoints');

        $this->post('/access-points/restore/' . self::ARCHIVED_ACCESS_POINT_ID);

        $this->assertRedirect();
        $this->assertNull($accessPoints->get(self::ARCHIVED_ACCESS_POINT_ID)->get('archived'));
    }

    /**
     * Neither action answers a plain visit. Putting a record away is a change like any other, and a
     * link that could be followed by anything crawling the pages would make it one.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::archive()
     * @link \App\Controller\AccessPointsController::restore()
     */
    public function testArchivingAndRestoringAreNotAnsweredToAPlainVisit(): void
    {
        $this->login();

        $this->get('/access-points/archive/' . self::ACTIVE_ACCESS_POINT_ID);
        $this->assertResponseCode(405);

        $this->get('/access-points/restore/' . self::ARCHIVED_ACCESS_POINT_ID);
        $this->assertResponseCode(405);
    }

    /**
     * The options the operator ticks are taken and the query is built to match. Each option pulls in
     * a further set of records, so asking for all of them at once is what says they can be combined
     * rather than only used one at a time.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::map()
     */
    public function testMapTakesTheOptionsItIsGiven(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/access-points/map', [
            'routeros_ip_links' => 1,
            'routeros_wireless_links' => 1,
            'radio_links' => 1,
            'linked_customers' => 1,
        ]);

        $this->assertResponseOk();
    }

    /**
     * An access point points at its own map: the nesting says which one and the query says what
     * to draw, so the page opens showing everything that reaches it.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::map()
     */
    public function testMapNestedUnderAnAccessPointDrawsEveryLayerReachingIt(): void
    {
        $map = $this->createMapTopology();

        $this->login();
        $this->get('/access-points/' . $map['home_access_point_id'] . '/map?' . http_build_query([
            'radio_links' => 1,
            'routeros_ip_links' => 1,
            'routeros_wireless_links' => 1,
            'linked_customers' => 1,
        ]));

        $this->assertResponseOk();
        $this->assertSame(
            $map['home_access_point_id'],
            $this->viewVariable('mapOptions')->getData('access_point_id'),
            'The access point comes from the route rather than from the form.',
        );

        // The far ends of every layer, which none of them would carry on its own.
        $drawn = $this->markersDrawn();
        $this->assertArrayHasKey($map['home_access_point_id'], $drawn);
        $this->assertArrayHasKey($map['neighbouring_access_point_id'], $drawn);
        $this->assertArrayHasKey($map['wired_customer_point_id'], $drawn);
        $this->assertArrayHasKey($map['wireless_customer_point_id'], $drawn);
    }

    /**
     * Narrowing the map to one access point narrows the device list offered along with it, so that
     * the second filter cannot be set to a device the first one has already ruled out.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::map()
     */
    public function testMapNarrowedToOneAccessPointOffersOnlyItsDevices(): void
    {
        $map = $this->createMapTopology();

        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/access-points/map', [
            'access_point_id' => $map['home_access_point_id'],
            'routeros_device_id' => $map['home_device_id'],
        ]);

        $this->assertResponseOk();
        $this->assertSame(
            [$map['home_device_id']],
            array_keys((array)$this->viewVariable('routerosDevicesFilter')->toArray()),
        );
        $this->assertSame([$map['home_access_point_id']], array_keys($this->markersDrawn()));
    }

    /**
     * Two access points linked by an IP address out of one network are drawn joined up: a line
     * between them and a marker on each end.
     *
     * One line, not one per end. Both ends are walked, because both stand at an access point the
     * map draws, and the link is recorded on both of them.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::map()
     */
    public function testMapDrawsAnIpLinkBetweenTwoAccessPoints(): void
    {
        $map = $this->createMapTopology();

        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/access-points/map', ['routeros_ip_links' => 1]);

        $this->assertResponseOk();
        $this->assertArrayHasKey(
            $this->lineKey($map['home_access_point_id'], $map['neighbouring_access_point_id']),
            $this->polylinesDrawn(),
        );
        $this->assertArrayHasKey($map['neighbouring_access_point_id'], $this->markersDrawn());
        $this->assertCount(1, $this->polylinesDrawn(), 'The link is drawn once from each end.');
    }

    /**
     * The same holds for a wireless link, which is found by the two ends naming each other rather
     * than by them sharing a network.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::map()
     */
    public function testMapDrawsAWirelessLinkBetweenTwoAccessPoints(): void
    {
        $map = $this->createMapTopology();

        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/access-points/map', ['routeros_wireless_links' => 1]);

        $this->assertResponseOk();
        $this->assertArrayHasKey(
            $this->lineKey($map['home_access_point_id'], $map['neighbouring_access_point_id']),
            $this->polylinesDrawn(),
        );
        $this->assertCount(1, $this->polylinesDrawn(), 'The link is drawn once from each end.');
    }

    /**
     * A customer hanging off the access point is drawn only when the operator asked for customers.
     * There are far more of them than there are access points, so they are left off by default.
     *
     * Both kinds of link are asked about, because a customer is reached over whichever one their
     * device happens to be on.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::map()
     */
    public function testMapDrawsACustomerOnlyWhenAskedTo(): void
    {
        $map = $this->createMapTopology();

        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/access-points/map', ['routeros_ip_links' => 1, 'routeros_wireless_links' => 1]);
        $this->assertArrayNotHasKey($map['wired_customer_point_id'], $this->markersDrawn());
        $this->assertArrayNotHasKey($map['wireless_customer_point_id'], $this->markersDrawn());

        $this->post('/access-points/map', [
            'routeros_ip_links' => 1,
            'routeros_wireless_links' => 1,
            'linked_customers' => 1,
        ]);

        $this->assertResponseOk();
        foreach (['wired_customer_point_id', 'wireless_customer_point_id'] as $customerPoint) {
            $this->assertArrayHasKey($map[$customerPoint], $this->markersDrawn());
            $this->assertArrayHasKey(
                $this->lineKey($map['home_access_point_id'], $map[$customerPoint]),
                $this->polylinesDrawn(),
            );
        }
    }

    /**
     * A radio link between two access points is drawn as one line, not as one per end.
     *
     * Both ends are walked, because both stand at an access point the map draws, and a line laid
     * down twice under two keys is two lines over each other on the map.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::map()
     */
    public function testMapDrawsARadioLinkBetweenTwoAccessPointsOnce(): void
    {
        $map = $this->createMapTopology();

        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/access-points/map', ['radio_links' => 1]);

        $this->assertResponseOk();
        $this->assertArrayHasKey(
            $this->lineKey(
                $map['home_access_point_id'],
                $map['neighbouring_access_point_id'],
                $map['backhaul_radio_link_id'],
            ),
            $this->polylinesDrawn(),
        );
        $this->assertArrayHasKey($map['neighbouring_access_point_id'], $this->markersDrawn());
        $this->assertCount(1, $this->linesOfRadioLink($map['backhaul_radio_link_id']));

        // the link hands back every unit on it, so the unit walked in on is offered as a far end of
        // its own link, and it would be listed against itself
        $this->assertSame(
            1,
            substr_count(
                $this->markersDrawn()[$map['home_access_point_id']]->content,
                'Map backhaul home end',
            ),
            'The unit is named as the far end of its own link.',
        );
    }

    /**
     * The end of a radio link standing at a customer follows the switch the other layers follow -
     * one switch for customers, whichever kind of link they hang off.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::map()
     */
    public function testMapDrawsARadioLinkToACustomerOnlyWhenAskedTo(): void
    {
        $map = $this->createMapTopology();

        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/access-points/map', ['radio_links' => 1]);
        $this->assertArrayNotHasKey($map['radio_customer_point_id'], $this->markersDrawn());

        $this->post('/access-points/map', ['radio_links' => 1, 'linked_customers' => 1]);

        $this->assertResponseOk();
        $this->assertArrayHasKey($map['radio_customer_point_id'], $this->markersDrawn());
        $this->assertArrayHasKey(
            $this->lineKey(
                $map['home_access_point_id'],
                $map['radio_customer_point_id'],
                $map['customer_radio_link_id'],
            ),
            $this->polylinesDrawn(),
        );
    }

    /**
     * A link recorded with more than two units is a sector serving several clients, and the clients
     * are not joined to one another - they cannot see each other and no line says they can.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::map()
     */
    public function testMapDrawsAMultipointRadioLinkOnlyOutOfTheAccessPoint(): void
    {
        $map = $this->createMapTopology();

        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/access-points/map', ['radio_links' => 1, 'linked_customers' => 1]);

        $this->assertResponseOk();

        $lines = $this->linesOfRadioLink($map['multipoint_radio_link_id']);

        $this->assertSame(
            [
                $this->lineKey(
                    $map['home_access_point_id'],
                    $map['radio_customer_point_id'],
                    $map['multipoint_radio_link_id'],
                ),
                $this->lineKey(
                    $map['home_access_point_id'],
                    $map['shared_customer_point_id'],
                    $map['multipoint_radio_link_id'],
                ),
            ],
            array_keys($lines),
        );
    }

    /**
     * A radio link is drawn however long ago it was written down.
     *
     * The layers read off the devices leave out anything not heard from within the fortnight,
     * because a stale reading is not to be trusted. A radio link is not read off anything - it is
     * what somebody recorded - so the same window would hide the links nobody has had to touch.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::map()
     */
    public function testMapDrawsARadioLinkHoweverOldTheRecordIs(): void
    {
        $map = $this->createMapTopology();

        $this->getTableLocator()->get('RadioUnits')
            ->updateAll(['modified' => new DateTime('-2 years')], []);
        $this->getTableLocator()->get('RadioLinks')
            ->updateAll(['modified' => new DateTime('-2 years')], []);

        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/access-points/map', ['radio_links' => 1]);

        $this->assertResponseOk();
        $this->assertCount(1, $this->linesOfRadioLink($map['backhaul_radio_link_id']));
    }

    /**
     * No line is ever drawn from a place back to itself.
     *
     * The units of a link are reached through the link, which hands back the unit walked in on
     * along with the rest of them, and two units of one link may perfectly well stand on one mast.
     * Either one drawn as a link to somewhere would be a line of no length sitting under a marker.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::map()
     */
    public function testMapNeverDrawsALinkBackToWhereItStarted(): void
    {
        $this->createMapTopology();

        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/access-points/map', [
            'routeros_ip_links' => 1,
            'routeros_wireless_links' => 1,
            'radio_links' => 1,
            'linked_customers' => 1,
        ]);

        $this->assertResponseOk();

        foreach ($this->polylinesDrawn() as $key => $polyline) {
            $this->assertNotEquals(
                [$polyline->from->lat, $polyline->from->lng],
                [$polyline->to->lat, $polyline->to->lng],
                $key . ' joins a place to itself.',
            );
        }
    }

    /**
     * The lines drawn for one radio link, keyed as the map keys them.
     *
     * @param string $radioLinkId The link asked about.
     * @return array<string, \Maps\Polyline>
     */
    private function linesOfRadioLink(string $radioLinkId): array
    {
        return array_filter(
            $this->polylinesDrawn(),
            fn(string $key): bool => str_starts_with($key, 'link-' . $radioLinkId . '--'),
            ARRAY_FILTER_USE_KEY,
        );
    }

    /**
     * The key one line is held under, written the way the controller writes it.
     *
     * @param string $from One end.
     * @param string $to The other end.
     * @param string|null $link The link itself, where the layer keeps two of them apart.
     * @return string
     */
    private function lineKey(string $from, string $to, ?string $link = null): string
    {
        $ends = [$from, $to];
        sort($ends);

        return ($link !== null ? 'link-' . $link . '--' : '') . implode('--', $ends);
    }

    /**
     * The markers the map was handed, keyed by what they mark.
     *
     * @return array<string, \Maps\Marker>
     */
    private function markersDrawn(): array
    {
        /** @var array<string, \Maps\Marker> $markers */
        $markers = $this->viewVariable('mapMarkers');

        return $markers;
    }

    /**
     * The lines the map was handed, keyed by the two ends they join.
     *
     * @return array<string, \Maps\Polyline>
     */
    private function polylinesDrawn(): array
    {
        /** @var array<string, \Maps\Polyline> $polylines */
        $polylines = $this->viewVariable('mapPolylines');

        return $polylines;
    }

    /**
     * Puts up enough of a network for the map to have something to draw: two access points joined to
     * each other, and a customer hanging off the first one. Both ends of each link are recorded,
     * because a link is what the two ends say about each other rather than a record of its own.
     *
     * It is built here rather than put in the fixtures because the map only looks at devices heard
     * from within the last fortnight, and a fixture carrying a written-down timestamp falls outside
     * that window the moment it is written. Saving the records lets the timestamp behavior date them
     * to now.
     *
     * @return array<string, string> The ids of what was put up.
     */
    private function createMapTopology(): array
    {
        $accessPoints = $this->getTableLocator()->get('AccessPoints');
        $customerPoints = $this->getTableLocator()->get('CustomerPoints');
        $customerConnections = $this->getTableLocator()->get('CustomerConnections');
        $devices = $this->getTableLocator()->get('RouterosDevices');

        $accessPointTypeId = $this->firstId('AccessPointTypes');

        $home = $accessPoints->saveOrFail($accessPoints->newEntity([
            'name' => 'Map home',
            'gps_y' => 50.0875,
            'gps_x' => 14.4212,
            'access_point_type_id' => $accessPointTypeId,
        ]));
        $neighbour = $accessPoints->saveOrFail($accessPoints->newEntity([
            'name' => 'Map neighbour',
            'gps_y' => 50.7663,
            'gps_x' => 15.0543,
            'access_point_type_id' => $accessPointTypeId,
        ]));
        // one customer behind each kind of link: whichever link is walked first is the one that puts
        // the marker up, so a customer reachable both ways would leave the other way unasked
        $wiredCustomerPoint = $customerPoints->saveOrFail($customerPoints->newEntity([
            'name' => 'Map wired customer',
            'gps_y' => 50.4000,
            'gps_x' => 14.9000,
        ]));
        $wiredCustomerConnection = $customerConnections->saveOrFail($customerConnections->newEntity([
            'name' => 'Map wired customer connection',
            'customer_point_id' => $wiredCustomerPoint->get('id'),
        ]));
        $wirelessCustomerPoint = $customerPoints->saveOrFail($customerPoints->newEntity([
            'name' => 'Map wireless customer',
            'gps_y' => 50.3000,
            'gps_x' => 14.7000,
        ]));
        $wirelessCustomerConnection = $customerConnections->saveOrFail($customerConnections->newEntity([
            'name' => 'Map wireless customer connection',
            'customer_point_id' => $wirelessCustomerPoint->get('id'),
        ]));
        $radioCustomerPoint = $customerPoints->saveOrFail($customerPoints->newEntity([
            'name' => 'Map radio customer',
            'gps_y' => 50.2000,
            'gps_x' => 14.5000,
        ]));
        $radioCustomerConnection = $customerConnections->saveOrFail($customerConnections->newEntity([
            'name' => 'Map radio customer connection',
            'customer_point_id' => $radioCustomerPoint->get('id'),
        ]));
        // a second customer on the same radio link, so that a link recorded with more than two
        // units has something to be drawn wrongly as - a line between the two of them
        $sharedCustomerPoint = $customerPoints->saveOrFail($customerPoints->newEntity([
            'name' => 'Map shared radio customer',
            'gps_y' => 50.2500,
            'gps_x' => 14.5500,
        ]));
        $sharedCustomerConnection = $customerConnections->saveOrFail($customerConnections->newEntity([
            'name' => 'Map shared radio customer connection',
            'customer_point_id' => $sharedCustomerPoint->get('id'),
        ]));

        $homeDevice = $devices->saveOrFail($devices->newEntity([
            'name' => 'Map home device',
            'access_point_id' => $home->get('id'),
        ]));
        $neighbourDevice = $devices->saveOrFail($devices->newEntity([
            'name' => 'Map neighbour device',
            'access_point_id' => $neighbour->get('id'),
        ]));
        $wiredCustomerDevice = $devices->saveOrFail($devices->newEntity([
            'name' => 'Map wired customer device',
            'customer_connection_id' => $wiredCustomerConnection->get('id'),
        ]));
        $wirelessCustomerDevice = $devices->saveOrFail($devices->newEntity([
            'name' => 'Map wireless customer device',
            'customer_connection_id' => $wirelessCustomerConnection->get('id'),
        ]));

        // an IP link is two addresses out of one network, one on each device
        $this->recordAddress($homeDevice->get('id'), '10.0.0.1/30');
        $this->recordAddress($neighbourDevice->get('id'), '10.0.0.2/30');
        $this->recordAddress($homeDevice->get('id'), '10.0.1.1/30');
        $this->recordAddress($wiredCustomerDevice->get('id'), '10.0.1.2/30');

        // a wireless link is two interfaces on one ssid, each naming the other's address
        $this->recordWirelessInterface($homeDevice->get('id'), '00:11:22:33:44:01', '00:11:22:33:44:02', 'ap-link');
        $this->recordWirelessInterface(
            $neighbourDevice->get('id'),
            '00:11:22:33:44:02',
            '00:11:22:33:44:01',
            'ap-link',
        );
        $this->recordWirelessInterface(
            $homeDevice->get('id'),
            '00:11:22:33:44:03',
            '00:11:22:33:44:04',
            'customer-link',
        );
        $this->recordWirelessInterface(
            $wirelessCustomerDevice->get('id'),
            '00:11:22:33:44:04',
            '00:11:22:33:44:03',
            'customer-link',
        );

        // a radio link is written down rather than read, so both ends are simply recorded as being
        // on it - one link out to the neighbour, one out to a customer, and one serving two
        $backhaul = $this->recordRadioLink('Map backhaul', [
            ['name' => 'Map backhaul home end', 'access_point_id' => $home->get('id')],
            ['name' => 'Map backhaul far end', 'access_point_id' => $neighbour->get('id')],
        ]);
        $customerLink = $this->recordRadioLink('Map radio customer link', [
            ['name' => 'Map customer link home end', 'access_point_id' => $home->get('id')],
            [
                'name' => 'Map customer link far end',
                'customer_connection_id' => $radioCustomerConnection->get('id'),
            ],
        ]);
        $multipoint = $this->recordRadioLink('Map multipoint', [
            ['name' => 'Map multipoint sector', 'access_point_id' => $home->get('id')],
            [
                'name' => 'Map multipoint first client',
                'customer_connection_id' => $radioCustomerConnection->get('id'),
            ],
            [
                'name' => 'Map multipoint second client',
                'customer_connection_id' => $sharedCustomerConnection->get('id'),
            ],
        ]);

        return [
            'home_access_point_id' => (string)$home->get('id'),
            'neighbouring_access_point_id' => (string)$neighbour->get('id'),
            'wired_customer_point_id' => (string)$wiredCustomerPoint->get('id'),
            'wireless_customer_point_id' => (string)$wirelessCustomerPoint->get('id'),
            'radio_customer_point_id' => (string)$radioCustomerPoint->get('id'),
            'shared_customer_point_id' => (string)$sharedCustomerPoint->get('id'),
            'home_device_id' => (string)$homeDevice->get('id'),
            'backhaul_radio_link_id' => $backhaul,
            'customer_radio_link_id' => $customerLink,
            'multipoint_radio_link_id' => $multipoint,
        ];
    }

    /**
     * Record a radio link and the units standing on it, each placed where it stands.
     *
     * @param string $name What the link is called.
     * @param array<array<string, mixed>> $units The units on it, each with the place it stands at.
     * @return string The id of the link.
     */
    private function recordRadioLink(string $name, array $units): string
    {
        $radioLinks = $this->getTableLocator()->get('RadioLinks');
        $radioUnits = $this->getTableLocator()->get('RadioUnits');

        $radioLink = $radioLinks->saveOrFail($radioLinks->newEntity(['name' => $name]));
        $radioUnitTypeId = $this->firstId('RadioUnitTypes');

        foreach ($units as $unit) {
            $radioUnits->saveOrFail($radioUnits->newEntity($unit + [
                'radio_link_id' => $radioLink->get('id'),
                'radio_unit_type_id' => $radioUnitTypeId,
            ]));
        }

        return (string)$radioLink->get('id');
    }

    /**
     * Record an address a device answers at.
     *
     * @param string $deviceId Device the address is on.
     * @param string $address Address with its prefix; the network is derived from it.
     * @return void
     */
    private function recordAddress(string $deviceId, string $address): void
    {
        $addresses = $this->getTableLocator()->get('RouterosDeviceIps');

        $addresses->saveOrFail($addresses->newEntity([
            'routeros_device_id' => $deviceId,
            'name' => $address,
            'ip_address' => $address,
        ]));
    }

    /**
     * Record one end of a wireless link. The type is the one the pairing is written for, and the two
     * ends are matched by each naming the other's address on the same ssid.
     *
     * @param string $deviceId Device the interface is on.
     * @param string $macAddress Address of this end.
     * @param string $bssid Address of the end it is talking to.
     * @param string $ssid Network both ends are on.
     * @return void
     */
    private function recordWirelessInterface(
        string $deviceId,
        string $macAddress,
        string $bssid,
        string $ssid,
    ): void {
        $interfaces = $this->getTableLocator()->get('RouterosDeviceInterfaces');

        $interfaces->saveOrFail($interfaces->newEntity([
            'routeros_device_id' => $deviceId,
            'name' => 'wlan-' . $ssid,
            'mac_address' => $macAddress,
            'bssid' => $bssid,
            'ssid' => $ssid,
            'interface_type' => 71,
        ]));
    }
}
