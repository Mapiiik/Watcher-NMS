<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\CustomerConnectionsController;
use App\Test\Traits\ControllerTestTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Controller\CustomerConnectionsController Test Case
 *
 * Smoke tests: every action is requested once and has to answer. They are deliberately shallow -
 * their job is to notice an action that stopped answering at all, which is where the query building
 * bugs turn up.
 */
#[UsesClass(CustomerConnectionsController::class)]
class CustomerConnectionsControllerTest extends TestCase
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
        'app.CustomerPoints',
        'app.CustomerConnections',
        'app.CustomerConnectionIps',
        'app.AccessPointTypes',
        'app.AccessPoints',
        'app.DeviceTypes',
        'app.RouterosDevices',
    ];

    /**
     * The listing renders.
     *
     * @return void
     * @link \App\Controller\CustomerConnectionsController::index()
     */
    public function testIndex(): void
    {
        $this->login();
        $this->get('/customer-connections');

        $this->assertResponseOk();
    }

    /**
     * The listing renders with the search filled in, which builds a different query than the plain
     * listing does and is therefore worth requesting on its own.
     *
     * @return void
     * @link \App\Controller\CustomerConnectionsController::index()
     */
    public function testIndexWithSearch(): void
    {
        $this->login();
        $this->get('/customer-connections?search=Lorem');

        $this->assertResponseOk();
    }

    /**
     * The detail of a record renders.
     *
     * @return void
     * @link \App\Controller\CustomerConnectionsController::view()
     */
    public function testView(): void
    {
        $this->login();
        $this->get('/customer-connections/view/' . $this->firstId('CustomerConnections'));

        $this->assertResponseOk();
    }

    /**
     * The form for a new record renders.
     *
     * @return void
     * @link \App\Controller\CustomerConnectionsController::add()
     */
    public function testAdd(): void
    {
        $this->login();
        $this->get('/customer-connections/add');

        $this->assertResponseOk();
    }

    /**
     * The form of an existing record renders.
     *
     * @return void
     * @link \App\Controller\CustomerConnectionsController::edit()
     */
    public function testEdit(): void
    {
        $this->login();
        $this->get('/customer-connections/edit/' . $this->firstId('CustomerConnections'));

        $this->assertResponseOk();
    }

    /**
     * The delete action runs and redirects. Whether the record really goes depends on what else
     * still references it, which is the application rules' business rather than this test's.
     *
     * @return void
     * @link \App\Controller\CustomerConnectionsController::delete()
     */
    public function testDelete(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/customer-connections/delete/' . $this->firstId('CustomerConnections'));

        $this->assertRedirect();
    }

    /**
     * A connection filled in on the form is really stored.
     *
     * Rendering the form proves the page is there; this proves the way through it works.
     * Marshalling, validation, the application rules and the save only ever run on a request that
     * carries data.
     *
     * @return void
     * @link \App\Controller\CustomerConnectionsController::add()
     */
    public function testAddStoresAConnection(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/customer-connections/add', [
            'name' => 'Village link',
            'customer_point_id' => $this->firstId('CustomerPoints'),
            'access_point_id' => $this->firstId('AccessPoints'),
        ]);

        $this->assertRedirect();
        /** @var \App\Model\Entity\CustomerConnection $stored */
        $stored = $this->getTableLocator()->get('CustomerConnections')
            ->find()
            ->where(['name' => 'Village link'])
            ->firstOrFail();
        $this->assertSame($this->firstId('AccessPoints'), $stored->access_point_id);
    }

    /**
     * A connection pointed at a customer point that is not there is not stored, and the operator is
     * given the form back rather than a redirect that would suggest it went through.
     *
     * @return void
     * @link \App\Controller\CustomerConnectionsController::add()
     */
    public function testAddRefusesAConnectionOnACustomerPointThatIsNotThere(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $connections = $this->getTableLocator()->get('CustomerConnections');
        $before = $connections->find()->count();

        $this->post('/customer-connections/add', [
            'name' => 'Village link',
            'customer_point_id' => '3f2b1a0c-0000-4000-8000-000000000000',
        ]);

        $this->assertResponseOk();
        $this->assertSame($before, $connections->find()->count());
    }

    /**
     * A change made on the form reaches the record.
     *
     * @return void
     * @link \App\Controller\CustomerConnectionsController::edit()
     */
    public function testEditStoresTheChange(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $connectionId = $this->firstId('CustomerConnections');
        $this->post('/customer-connections/edit/' . $connectionId, ['name' => 'Renamed link']);

        $this->assertRedirect();
        $this->assertSame(
            'Renamed link',
            $this->getTableLocator()->get('CustomerConnections')->get($connectionId)->name,
        );
    }

    /**
     * A connection that was put away is brought back into use. The fixtures leave the one connection
     * they carry archived, so this is the way round that can be asked of it first.
     *
     * @return void
     * @link \App\Controller\CustomerConnectionsController::restore()
     */
    public function testRestoreBringsTheConnectionBackIntoUse(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $customerConnections = $this->getTableLocator()->get('CustomerConnections');
        $connectionId = $this->firstId('CustomerConnections');

        $this->post('/customer-connections/restore/' . $connectionId);

        $this->assertRedirect();
        $this->assertNull($customerConnections->get($connectionId)->get('archived'));
    }

    /**
     * A connection that is no longer in use is put away rather than taken away: it is marked as
     * archived, and it is still there afterwards.
     *
     * @return void
     * @link \App\Controller\CustomerConnectionsController::archive()
     */
    public function testArchivePutsTheConnectionAwayWithoutTakingItAway(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $customerConnections = $this->getTableLocator()->get('CustomerConnections');
        $connectionId = $this->firstId('CustomerConnections');
        $customerConnections->restore($customerConnections->get($connectionId));

        $this->post('/customer-connections/archive/' . $connectionId);

        $this->assertRedirect();
        $this->assertNotNull($customerConnections->get($connectionId)->get('archived'));
    }

    /**
     * Neither action answers a plain visit. Putting a record away is a change like any other, and a
     * link that could be followed by anything crawling the pages would make it one.
     *
     * @return void
     * @link \App\Controller\CustomerConnectionsController::archive()
     * @link \App\Controller\CustomerConnectionsController::restore()
     */
    public function testArchivingAndRestoringAreNotAnsweredToAPlainVisit(): void
    {
        $this->login();
        $connectionId = $this->firstId('CustomerConnections');

        $this->get('/customer-connections/archive/' . $connectionId);
        $this->assertResponseCode(405);

        $this->get('/customer-connections/restore/' . $connectionId);
        $this->assertResponseCode(405);
    }
}
