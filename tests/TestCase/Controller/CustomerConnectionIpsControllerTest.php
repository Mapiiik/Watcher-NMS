<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\CustomerConnectionIpsController;
use App\Test\Traits\ControllerTestTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Controller\CustomerConnectionIpsController Test Case
 *
 * Smoke tests: every action is requested once and has to answer. They are deliberately shallow -
 * their job is to notice an action that stopped answering at all, which is where the query building
 * bugs turn up.
 */
#[UsesClass(CustomerConnectionIpsController::class)]
class CustomerConnectionIpsControllerTest extends TestCase
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
    ];

    /**
     * The listing renders.
     *
     * @return void
     * @link \App\Controller\CustomerConnectionIpsController::index()
     */
    public function testIndex(): void
    {
        $this->login();
        $this->get('/customer-connection-ips');

        $this->assertResponseOk();
    }

    /**
     * The listing renders with the search filled in, which builds a different query than the plain
     * listing does and is therefore worth requesting on its own.
     *
     * @return void
     * @link \App\Controller\CustomerConnectionIpsController::index()
     */
    public function testIndexWithSearch(): void
    {
        $this->login();
        $this->get('/customer-connection-ips?search=Lorem');

        $this->assertResponseOk();
    }

    /**
     * The detail of a record renders.
     *
     * @return void
     * @link \App\Controller\CustomerConnectionIpsController::view()
     */
    public function testView(): void
    {
        $this->login();
        $this->get('/customer-connection-ips/view/' . $this->firstId('CustomerConnectionIps'));

        $this->assertResponseOk();
    }

    /**
     * The form for a new record renders.
     *
     * @return void
     * @link \App\Controller\CustomerConnectionIpsController::add()
     */
    public function testAdd(): void
    {
        $this->login();
        $this->get('/customer-connection-ips/add');

        $this->assertResponseOk();
    }

    /**
     * The form of an existing record renders.
     *
     * @return void
     * @link \App\Controller\CustomerConnectionIpsController::edit()
     */
    public function testEdit(): void
    {
        $this->login();
        $this->get('/customer-connection-ips/edit/' . $this->firstId('CustomerConnectionIps'));

        $this->assertResponseOk();
    }

    /**
     * The delete action runs and redirects. Whether the record really goes depends on what else
     * still references it, which is the application rules' business rather than this test's.
     *
     * @return void
     * @link \App\Controller\CustomerConnectionIpsController::delete()
     */
    public function testDelete(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/customer-connection-ips/delete/' . $this->firstId('CustomerConnectionIps'));

        $this->assertRedirect();
    }

    /**
     * An address filled in on the form is really stored. Rendering the form proves the page is
     * there; marshalling, validation, the application rules and the save only ever run on a request
     * that carries data.
     *
     * @return void
     * @link \App\Controller\CustomerConnectionIpsController::add()
     */
    public function testAddStoresAnAddress(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/customer-connection-ips/add', [
            'name' => 'Village uplink address',
            'ip_address' => '10.20.30.40',
            'customer_connection_id' => $this->firstId('CustomerConnections'),
        ]);

        $this->assertRedirect();
        /** @var \App\Model\Entity\CustomerConnectionIp $stored */
        $stored = $this->getTableLocator()->get('CustomerConnectionIps')
            ->find()
            ->where(['name' => 'Village uplink address'])
            ->firstOrFail();
        $this->assertSame($this->firstId('CustomerConnections'), $stored->customer_connection_id);
    }

    /**
     * An address on a connection that is not there is not stored, and the operator is given the
     * form back rather than a redirect that would suggest it went through.
     *
     * @return void
     * @link \App\Controller\CustomerConnectionIpsController::add()
     */
    public function testAddRefusesAnAddressOnAConnectionThatIsNotThere(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $customerConnectionIps = $this->getTableLocator()->get('CustomerConnectionIps');
        $before = $customerConnectionIps->find()->count();

        $this->post('/customer-connection-ips/add', [
            'name' => 'Village uplink address',
            'ip_address' => '10.20.30.40',
            'customer_connection_id' => '3f2b1a0c-0000-4000-8000-000000000000',
        ]);

        $this->assertResponseOk();
        $this->assertSame($before, $customerConnectionIps->find()->count());
    }

    /**
     * A change made on the form reaches the record.
     *
     * @return void
     * @link \App\Controller\CustomerConnectionIpsController::edit()
     */
    public function testEditStoresTheChange(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $customerConnectionIpId = $this->firstId('CustomerConnectionIps');
        $this->post('/customer-connection-ips/edit/' . $customerConnectionIpId, ['name' => 'Renamed address']);

        $this->assertRedirect();
        $this->assertSame(
            'Renamed address',
            $this->getTableLocator()->get('CustomerConnectionIps')->get($customerConnectionIpId)->name,
        );
    }
}
