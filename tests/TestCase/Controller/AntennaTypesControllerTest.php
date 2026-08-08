<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\AntennaTypesController;
use App\Test\Traits\ControllerTestTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Controller\AntennaTypesController Test Case
 *
 * Smoke tests: every action is requested once and has to answer. They are deliberately shallow -
 * their job is to notice an action that stopped answering at all, which is where the query building
 * bugs turn up.
 */
#[UsesClass(AntennaTypesController::class)]
class AntennaTypesControllerTest extends TestCase
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
        'app.AccessPointTypes',
        'app.AccessPoints',
        'app.RadioLinks',
        'app.RadioUnitTypes',
        'app.RadioUnits',
    ];

    /**
     * The listing renders.
     *
     * @return void
     * @link \App\Controller\AntennaTypesController::index()
     */
    public function testIndex(): void
    {
        $this->login();
        $this->get('/antenna-types');

        $this->assertResponseOk();
    }

    /**
     * The listing renders with the search filled in, which builds a different query than the plain
     * listing does and is therefore worth requesting on its own.
     *
     * @return void
     * @link \App\Controller\AntennaTypesController::index()
     */
    public function testIndexWithSearch(): void
    {
        $this->login();
        $this->get('/antenna-types?search=Lorem');

        $this->assertResponseOk();
    }

    /**
     * The detail of a record renders.
     *
     * @return void
     * @link \App\Controller\AntennaTypesController::view()
     */
    public function testView(): void
    {
        $this->login();
        $this->get('/antenna-types/view/' . $this->firstId('AntennaTypes'));

        $this->assertResponseOk();
    }

    /**
     * The form for a new record renders.
     *
     * @return void
     * @link \App\Controller\AntennaTypesController::add()
     */
    public function testAdd(): void
    {
        $this->login();
        $this->get('/antenna-types/add');

        $this->assertResponseOk();
    }

    /**
     * The form of an existing record renders.
     *
     * @return void
     * @link \App\Controller\AntennaTypesController::edit()
     */
    public function testEdit(): void
    {
        $this->login();
        $this->get('/antenna-types/edit/' . $this->firstId('AntennaTypes'));

        $this->assertResponseOk();
    }

    /**
     * The delete action runs and redirects. Whether the record really goes depends on what else
     * still references it, which is the application rules' business rather than this test's.
     *
     * @return void
     * @link \App\Controller\AntennaTypesController::delete()
     */
    public function testDelete(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/antenna-types/delete/' . $this->firstId('AntennaTypes'));

        $this->assertRedirect();
    }

    /**
     * An antenna type filled in on the form is really stored. Rendering the form proves the page is
     * there; marshalling, validation, the application rules and the save only ever run on a request
     * that carries data.
     *
     * @return void
     * @link \App\Controller\AntennaTypesController::add()
     */
    public function testAddStoresAnAntennaType(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/antenna-types/add', [
            'name' => 'Sector 90',
            'antenna_gain' => '17',
            'part_number' => 'ANT-90-17',
            'manufacturer_id' => $this->firstId('Manufacturers'),
            'radio_unit_band_id' => $this->firstId('RadioUnitBands'),
        ]);

        $this->assertRedirect();
        /** @var \App\Model\Entity\AntennaType $stored */
        $stored = $this->getTableLocator()->get('AntennaTypes')
            ->find()
            ->where(['name' => 'Sector 90'])
            ->firstOrFail();
        $this->assertSame($this->firstId('Manufacturers'), $stored->manufacturer_id);
    }

    /**
     * An antenna type of a manufacturer that is not there is not stored, and the operator is given
     * the form back rather than a redirect that would suggest it went through.
     *
     * @return void
     * @link \App\Controller\AntennaTypesController::add()
     */
    public function testAddRefusesAnAntennaTypeOfAManufacturerThatIsNotThere(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $antennaTypes = $this->getTableLocator()->get('AntennaTypes');
        $before = $antennaTypes->find()->count();

        $this->post('/antenna-types/add', [
            'name' => 'Sector 90',
            'manufacturer_id' => '3f2b1a0c-0000-4000-8000-000000000000',
        ]);

        $this->assertResponseOk();
        $this->assertSame($before, $antennaTypes->find()->count());
    }

    /**
     * A change made on the form reaches the record.
     *
     * @return void
     * @link \App\Controller\AntennaTypesController::edit()
     */
    public function testEditStoresTheChange(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $antennaTypeId = $this->firstId('AntennaTypes');
        $this->post('/antenna-types/edit/' . $antennaTypeId, ['name' => 'Renamed antenna type']);

        $this->assertRedirect();
        $this->assertSame(
            'Renamed antenna type',
            $this->getTableLocator()->get('AntennaTypes')->get($antennaTypeId)->name,
        );
    }
}
