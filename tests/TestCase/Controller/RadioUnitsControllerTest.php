<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\RadioUnitsController;
use App\Test\Traits\ControllerTestTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Controller\RadioUnitsController Test Case
 *
 * Smoke tests: every action is requested once and has to answer. They are deliberately shallow -
 * their job is to notice an action that stopped answering at all, which is where the query building
 * bugs turn up.
 */
#[UsesClass(RadioUnitsController::class)]
class RadioUnitsControllerTest extends TestCase
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
     * @link \App\Controller\RadioUnitsController::index()
     */
    public function testIndex(): void
    {
        $this->login();
        $this->get('/radio-units');

        $this->assertResponseOk();
    }

    /**
     * The listing renders with the search filled in, which builds a different query than the plain
     * listing does and is therefore worth requesting on its own.
     *
     * @return void
     * @link \App\Controller\RadioUnitsController::index()
     */
    public function testIndexWithSearch(): void
    {
        $this->login();
        $this->get('/radio-units?search=Lorem');

        $this->assertResponseOk();
    }

    /**
     * The detail of a record renders.
     *
     * @return void
     * @link \App\Controller\RadioUnitsController::view()
     */
    public function testView(): void
    {
        $this->login();
        $this->get('/radio-units/view/' . $this->firstId('RadioUnits'));

        $this->assertResponseOk();
    }

    /**
     * The form for a new record renders.
     *
     * @return void
     * @link \App\Controller\RadioUnitsController::add()
     */
    public function testAdd(): void
    {
        $this->login();
        $this->get('/radio-units/add');

        $this->assertResponseOk();
    }

    /**
     * The form of an existing record renders.
     *
     * @return void
     * @link \App\Controller\RadioUnitsController::edit()
     */
    public function testEdit(): void
    {
        $this->login();
        $this->get('/radio-units/edit/' . $this->firstId('RadioUnits'));

        $this->assertResponseOk();
    }

    /**
     * The delete action runs and redirects. Whether the record really goes depends on what else
     * still references it, which is the application rules' business rather than this test's.
     *
     * @return void
     * @link \App\Controller\RadioUnitsController::delete()
     */
    public function testDelete(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/radio-units/delete/' . $this->firstId('RadioUnits'));

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
     * @link \App\Controller\RadioUnitsController::add()
     */
    public function testAddUnderTheRouteFilesItUnderTheRoute(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $before = $this->idsIn('RadioUnits');
        $this->post('/access-points/' . self::ACCESS_POINT_ID . '/radio-units/add', [
            'name' => 'Nested radio unit',
        ]);

        $this->assertRedirect();
        $added = $this->addedRecord('RadioUnits', $before);
        $this->assertSame(self::ACCESS_POINT_ID, $added->get('access_point_id'));
    }

    /**
     * Asked for under an access point the record does not belong to, it is answered where it does.
     *
     * The nested routes match any id against any record, so such a URL used to render the record
     * under a heading naming an access point it has nothing to do with. It is not an error - the
     * record exists and the caller is welcome to it - so the caller is sent to it.
     *
     * @return void
     * @link \App\Controller\AppController::beforeFilter()
     */
    public function testViewUnderAnotherAccessPointRedirectsToItsOwn(): void
    {
        $id = $this->firstId('RadioUnits');
        $this->login();
        $this->get('/access-points/' . self::ACCESS_POINT_ID . '/radio-units/view/' . $id);

        $this->assertRedirect('/access-points/1ec58677-1213-4950-80c4-bc1de41ea133/radio-units/view/' . $id);
    }

    /**
     * Asked for under the access point it belongs to, the record is answered there.
     *
     * @return void
     * @link \App\Controller\AppController::beforeFilter()
     */
    public function testViewUnderItsOwnAccessPointIsAnsweredThere(): void
    {
        $this->login();
        $this->get(
            '/access-points/1ec58677-1213-4950-80c4-bc1de41ea133/radio-units/view/'
            . $this->firstId('RadioUnits'),
        );

        $this->assertResponseOk();
    }
}
