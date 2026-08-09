<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\AccessPointsController;
use App\Test\Traits\ControllerTestTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Controller\AccessPointsController Test Case
 */
#[UsesClass(AccessPointsController::class)]
class AccessPointsControllerTest extends TestCase
{
    use ControllerTestTrait;
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
    ];

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
        $this->assertResponseContains('<a href="/access-points/' . $child->id . '">Tree child</a>');

        $this->get('/access-points/view/' . $child->id);

        $this->assertResponseOk();
        // The path leads from the root down to the access point itself.
        $this->assertResponseContains('Tree root</a> &gt; Tree child');
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
}
