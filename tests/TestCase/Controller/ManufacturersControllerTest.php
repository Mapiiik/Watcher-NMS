<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\ManufacturersController;
use App\Test\Traits\ControllerTestTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Controller\ManufacturersController Test Case
 *
 * Smoke tests: every action is requested once and has to answer. They are deliberately shallow -
 * their job is to notice an action that stopped answering at all, which is where the query building
 * bugs turn up.
 */
#[UsesClass(ManufacturersController::class)]
class ManufacturersControllerTest extends TestCase
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
        'app.Manufacturers',
        'app.RadioUnitBands',
        'app.AntennaTypes',
        'app.PowerSupplyTypes',
        'app.RadioUnitTypes',
    ];

    /**
     * The listing renders.
     *
     * @return void
     * @link \App\Controller\ManufacturersController::index()
     */
    public function testIndex(): void
    {
        $this->login();
        $this->get('/manufacturers');

        $this->assertResponseOk();
    }

    /**
     * The listing renders with the search filled in, which builds a different query than the plain
     * listing does and is therefore worth requesting on its own.
     *
     * @return void
     * @link \App\Controller\ManufacturersController::index()
     */
    public function testIndexWithSearch(): void
    {
        $this->login();
        $this->get('/manufacturers?search=Lorem');

        $this->assertResponseOk();
    }

    /**
     * The detail of a record renders.
     *
     * @return void
     * @link \App\Controller\ManufacturersController::view()
     */
    public function testView(): void
    {
        $this->login();
        $this->get('/manufacturers/view/' . $this->firstId('Manufacturers'));

        $this->assertResponseOk();
    }

    /**
     * The form for a new record renders.
     *
     * @return void
     * @link \App\Controller\ManufacturersController::add()
     */
    public function testAdd(): void
    {
        $this->login();
        $this->get('/manufacturers/add');

        $this->assertResponseOk();
    }

    /**
     * The form of an existing record renders.
     *
     * @return void
     * @link \App\Controller\ManufacturersController::edit()
     */
    public function testEdit(): void
    {
        $this->login();
        $this->get('/manufacturers/edit/' . $this->firstId('Manufacturers'));

        $this->assertResponseOk();
    }

    /**
     * The delete action runs and redirects. Whether the record really goes depends on what else
     * still references it, which is the application rules' business rather than this test's.
     *
     * @return void
     * @link \App\Controller\ManufacturersController::delete()
     */
    public function testDelete(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/manufacturers/delete/' . $this->firstId('Manufacturers'));

        $this->assertRedirect();
    }

    /**
     * A manufacturer filled in on the form is really stored. Rendering the form proves the page is
     * there; marshalling, validation, the application rules and the save only ever run on a request
     * that carries data.
     *
     * There is no counterpart refusing a manufacturer, because the model asks nothing of one - see
     * the note on {@see \App\Test\TestCase\Model\Table\ManufacturersTableTest::testValidationDefault()}.
     *
     * @return void
     * @link \App\Controller\ManufacturersController::add()
     */
    public function testAddStoresAManufacturer(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/manufacturers/add', [
            'name' => 'Northern Radio Works',
            'note' => 'Supplies the sector antennas.',
        ]);

        $this->assertRedirect();
        /** @var \App\Model\Entity\Manufacturer $stored */
        $stored = $this->getTableLocator()->get('Manufacturers')
            ->find()
            ->where(['name' => 'Northern Radio Works'])
            ->firstOrFail();
        $this->assertSame('Supplies the sector antennas.', $stored->note);
    }

    /**
     * A change made on the form reaches the record.
     *
     * @return void
     * @link \App\Controller\ManufacturersController::edit()
     */
    public function testEditStoresTheChange(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $manufacturerId = $this->firstId('Manufacturers');
        $this->post('/manufacturers/edit/' . $manufacturerId, ['name' => 'Renamed manufacturer']);

        $this->assertRedirect();
        $this->assertSame(
            'Renamed manufacturer',
            $this->getTableLocator()->get('Manufacturers')->get($manufacturerId)->name,
        );
    }
}
