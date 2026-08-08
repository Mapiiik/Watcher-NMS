<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\CustomerPointsController;
use App\Test\Traits\ControllerTestTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Controller\CustomerPointsController Test Case
 *
 * Smoke tests: every action is requested once and has to answer. They are deliberately shallow -
 * their job is to notice an action that stopped answering at all, which is where the query building
 * bugs turn up.
 */
#[UsesClass(CustomerPointsController::class)]
class CustomerPointsControllerTest extends TestCase
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
    ];

    /**
     * The listing renders.
     *
     * @return void
     * @link \App\Controller\CustomerPointsController::index()
     */
    public function testIndex(): void
    {
        $this->login();
        $this->get('/customer-points');

        $this->assertResponseOk();
    }

    /**
     * The listing renders with the search filled in, which builds a different query than the plain
     * listing does and is therefore worth requesting on its own.
     *
     * @return void
     * @link \App\Controller\CustomerPointsController::index()
     */
    public function testIndexWithSearch(): void
    {
        $this->login();
        $this->get('/customer-points?search=Lorem');

        $this->assertResponseOk();
    }

    /**
     * The detail of a record renders.
     *
     * @return void
     * @link \App\Controller\CustomerPointsController::view()
     */
    public function testView(): void
    {
        $this->login();
        $this->get('/customer-points/view/' . $this->firstId('CustomerPoints'));

        $this->assertResponseOk();
    }

    /**
     * The form for a new record renders.
     *
     * @return void
     * @link \App\Controller\CustomerPointsController::add()
     */
    public function testAdd(): void
    {
        $this->login();
        $this->get('/customer-points/add');

        $this->assertResponseOk();
    }

    /**
     * The form of an existing record renders.
     *
     * @return void
     * @link \App\Controller\CustomerPointsController::edit()
     */
    public function testEdit(): void
    {
        $this->login();
        $this->get('/customer-points/edit/' . $this->firstId('CustomerPoints'));

        $this->assertResponseOk();
    }

    /**
     * The delete action runs and redirects. Whether the record really goes depends on what else
     * still references it, which is the application rules' business rather than this test's.
     *
     * @return void
     * @link \App\Controller\CustomerPointsController::delete()
     */
    public function testDelete(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/customer-points/delete/' . $this->firstId('CustomerPoints'));

        $this->assertRedirect();
    }

    /**
     * A point filled in on the form is really stored. Rendering the form proves the page is there;
     * marshalling, validation, the application rules and the save only ever run on a request that
     * carries data.
     *
     * @return void
     * @link \App\Controller\CustomerPointsController::add()
     */
    public function testAddStoresAPoint(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/customer-points/add', [
            'name' => 'Hillside house',
            'gps_x' => '14.4212',
            'gps_y' => '50.0875',
        ]);

        $this->assertRedirect();
        /** @var \App\Model\Entity\CustomerPoint $stored */
        $stored = $this->getTableLocator()->get('CustomerPoints')
            ->find()
            ->where(['name' => 'Hillside house'])
            ->firstOrFail();
        $this->assertSame(14.4212, $stored->gps_x);
    }

    /**
     * A point whose coordinates are not numbers is not stored, and the operator is given the form
     * back rather than a redirect that would suggest it went through.
     *
     * @return void
     * @link \App\Controller\CustomerPointsController::add()
     */
    public function testAddRefusesAPointWithoutNumericCoordinates(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $customerPoints = $this->getTableLocator()->get('CustomerPoints');
        $before = $customerPoints->find()->count();

        $this->post('/customer-points/add', [
            'name' => 'Hillside house',
            'gps_x' => 'somewhere east',
            'gps_y' => '50.0875',
        ]);

        $this->assertResponseOk();
        $this->assertSame($before, $customerPoints->find()->count());
    }

    /**
     * A change made on the form reaches the record.
     *
     * @return void
     * @link \App\Controller\CustomerPointsController::edit()
     */
    public function testEditStoresTheChange(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $customerPointId = $this->firstId('CustomerPoints');
        $this->post('/customer-points/edit/' . $customerPointId, ['name' => 'Renamed point']);

        $this->assertRedirect();
        $this->assertSame(
            'Renamed point',
            $this->getTableLocator()->get('CustomerPoints')->get($customerPointId)->name,
        );
    }
}
