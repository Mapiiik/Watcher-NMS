<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\PowerSuppliesController;
use App\Test\Traits\ControllerTestTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Controller\PowerSuppliesController Test Case
 *
 * Smoke tests: every action is requested once and has to answer. They are deliberately shallow -
 * their job is to notice an action that stopped answering at all, which is where the query building
 * bugs turn up.
 */
#[UsesClass(PowerSuppliesController::class)]
class PowerSuppliesControllerTest extends TestCase
{
    use ControllerTestTrait;
    use IntegrationTestTrait;

    /**
     * Access point the nested routes hang off.
     *
     * @var string
     */
    private const ACCESS_POINT_ID = '1bd5e754-e102-46ad-8488-11b1b44bf026';

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.AppUsers',
        'app.AccessPointTypes',
        'app.AccessPoints',
        'app.Manufacturers',
        'app.PowerSupplyTypes',
        'app.PowerSupplies',
    ];

    /**
     * The listing renders.
     *
     * @return void
     * @link \App\Controller\PowerSuppliesController::index()
     */
    public function testIndex(): void
    {
        $this->login();
        $this->get('/power-supplies');

        $this->assertResponseOk();
    }

    /**
     * The listing renders with the search filled in, which builds a different query than the plain
     * listing does and is therefore worth requesting on its own.
     *
     * @return void
     * @link \App\Controller\PowerSuppliesController::index()
     */
    public function testIndexWithSearch(): void
    {
        $this->login();
        $this->get('/power-supplies?search=Lorem');

        $this->assertResponseOk();
    }

    /**
     * The detail of a record renders.
     *
     * @return void
     * @link \App\Controller\PowerSuppliesController::view()
     */
    public function testView(): void
    {
        $this->login();
        $this->get('/power-supplies/view/' . $this->firstId('PowerSupplies'));

        $this->assertResponseOk();
    }

    /**
     * The form for a new record renders.
     *
     * @return void
     * @link \App\Controller\PowerSuppliesController::add()
     */
    public function testAdd(): void
    {
        $this->login();
        $this->get('/power-supplies/add');

        $this->assertResponseOk();
    }

    /**
     * The form of an existing record renders.
     *
     * @return void
     * @link \App\Controller\PowerSuppliesController::edit()
     */
    public function testEdit(): void
    {
        $this->login();
        $this->get('/power-supplies/edit/' . $this->firstId('PowerSupplies'));

        $this->assertResponseOk();
    }

    /**
     * The delete action runs and redirects. Whether the record really goes depends on what else
     * still references it, which is the application rules' business rather than this test's.
     *
     * @return void
     * @link \App\Controller\PowerSuppliesController::delete()
     */
    public function testDelete(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/power-supplies/delete/' . $this->firstId('PowerSupplies'));

        $this->assertRedirect();
    }

    /**
     * Added under its access point, the record is filed under it without the form saying so.
     *
     * The form under an access point leaves that field out - the route already says which one it
     * is, and the controller fills it in. Posting it in the body instead, as a test reaching the
     * flat route does, asks a different question and leaves this one unasked.
     *
     * @return void
     * @link \App\Controller\PowerSuppliesController::add()
     */
    public function testAddUnderTheRouteFilesItUnderTheRoute(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $before = $this->idsIn('PowerSupplies');
        $this->post('/access-points/' . self::ACCESS_POINT_ID . '/power-supplies/add', [
            'name' => 'Nested power supply',
        ]);

        $this->assertRedirect();
        $added = $this->addedRecord('PowerSupplies', $before);
        $this->assertSame(self::ACCESS_POINT_ID, $added->get('access_point_id'));
    }

    /**
     * A change made on the form reaches the record.
     *
     * @return void
     * @link \App\Controller\PowerSuppliesController::edit()
     */
    public function testEditStoresTheChange(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $powerSupplyId = $this->firstId('PowerSupplies');
        $this->post('/power-supplies/edit/' . $powerSupplyId, ['name' => 'Renamed power supply']);

        $this->assertRedirect();
        $this->assertSame(
            'Renamed power supply',
            $this->getTableLocator()->get('PowerSupplies')->get($powerSupplyId)->name,
        );
    }
}
