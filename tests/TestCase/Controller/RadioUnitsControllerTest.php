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
        'app.Users',
        'app.Manufacturers',
        'app.RadioUnitBands',
        'app.AntennaTypes',
        'app.AccessPointTypes',
        'app.AccessPoints',
        'app.CustomerPoints',
        'app.CustomerConnections',
        'app.RadioLinks',
        'app.RadioUnitTypes',
        'app.RadioUnits',
    ];

    /**
     * The export holds what the listing it was opened from holds.
     *
     * @return void
     * @link \App\Controller\RadioUnitsController::export()
     */
    public function testTheExportIsNarrowedDownAsTheListingIs(): void
    {
        $band = $this->addBand();
        $types = $this->getTableLocator()->get('RadioUnitTypes');
        $type = $types->saveOrFail($types->newEntity(['name' => 'Exported type', 'radio_unit_band_id' => $band]));

        $radioUnits = $this->getTableLocator()->get('RadioUnits');
        $radioUnits->saveOrFail($radioUnits->newEntity([
            'name' => 'Unit under the route',
            'radio_unit_type_id' => $type->get('id'),
            'access_point_id' => self::ACCESS_POINT_ID,
        ]));

        $this->login();

        $this->get('/radio-units/export');
        $this->assertResponseOk();
        $this->assertSame(['Lorem ipsum dolor sit amet', 'Unit under the route'], $this->exported());

        $this->get('/access-points/' . self::ACCESS_POINT_ID . '/radio-units/export');
        $this->assertSame(['Unit under the route'], $this->exported());

        $this->get('/radio-units/export?search=under the route');
        $this->assertSame(['Unit under the route'], $this->exported());

        $this->get('/radio-units/export?radio_unit_band_id=' . $band);
        $this->assertSame(['Unit under the route'], $this->exported());
    }

    /**
     * The way to the export carries what the listing is narrowed down to, the nesting and the
     * search alike.
     *
     * @return void
     * @link \App\Controller\RadioUnitsController::index()
     */
    public function testTheListingLeadsToItsOwnExport(): void
    {
        $this->login();
        $this->get('/access-points/' . self::ACCESS_POINT_ID . '/radio-units?search=mast');

        $this->assertResponseOk();
        $this->assertResponseContains(
            'href="/access-points/' . self::ACCESS_POINT_ID . '/radio-units/export?search=mast"',
        );
    }

    /**
     * What an address edited by hand carries instead of an id narrows nothing down.
     *
     * @return void
     * @link \App\Controller\RadioUnitsController::index()
     */
    public function testABandThatIsNoIdIsLeftOut(): void
    {
        $this->login();
        $this->get('/radio-units?radio_unit_band_id=nonsense');

        $this->assertResponseOk();
    }

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
     * The form for a new record under an access point that is not there drops the nesting.
     *
     * This is what a bookmark turns into once the access point behind it is deleted. Left alone,
     * the form would fill the dead id in and the save fail on `existsIn`, complaining about a field
     * the form does not render - which reads as no reason at all.
     *
     * @return void
     * @link \App\Controller\AppController::beforeFilter()
     */
    public function testAddUnderAnAccessPointThatIsGoneDropsTheNesting(): void
    {
        $this->login();
        $this->get('/access-points/00000000-0000-4000-8000-000000000000/radio-units/add');

        $this->assertRedirect('/radio-units/add');
    }

    /**
     * The listing under an access point that is not there drops the nesting as well - it is the
     * address that is wrong, not the action asked for.
     *
     * @return void
     * @link \App\Controller\AppController::beforeFilter()
     */
    public function testIndexUnderAnAccessPointThatIsGoneDropsTheNesting(): void
    {
        $this->login();
        $this->get('/access-points/00000000-0000-4000-8000-000000000000/radio-units');

        $this->assertRedirect('/radio-units');
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

    /**
     * A change made on the form reaches the record.
     *
     * @return void
     * @link \App\Controller\RadioUnitsController::edit()
     */
    public function testEditStoresTheChange(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $radioUnitId = $this->firstId('RadioUnits');
        $this->post('/radio-units/edit/' . $radioUnitId, ['name' => 'Renamed radio unit']);

        $this->assertRedirect();
        $this->assertSame(
            'Renamed radio unit',
            $this->getTableLocator()->get('RadioUnits')->get($radioUnitId)->name,
        );
    }

    /**
     * A unit can be recorded as standing at a customer rather than at an access point, which is
     * what the client end of a link looks like.
     *
     * @return void
     * @link \App\Controller\RadioUnitsController::edit()
     */
    public function testEditStoresTheCustomerTheUnitStandsAt(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $connectionId = $this->firstId('CustomerConnections');
        $radioUnitId = $this->firstId('RadioUnits');

        $this->post('/radio-units/edit/' . $radioUnitId, [
            'name' => 'Unit at a customer',
            'customer_connection_id' => $connectionId,
        ]);

        $this->assertRedirect();
        $this->assertSame(
            $connectionId,
            $this->getTableLocator()->get('RadioUnits')->get($radioUnitId)->customer_connection_id,
        );
    }

    /**
     * An antenna of another band stays on offer for the unit that has it. Left out of the list,
     * saving a note would take the antenna away.
     *
     * @return void
     * @link \App\Controller\RadioUnitsController::edit()
     */
    public function testEditKeepsTheAntennaOfAnotherBand(): void
    {
        $this->login();
        $radioUnitId = $this->firstId('RadioUnits');
        $antennaTypes = $this->getTableLocator()->get('AntennaTypes');
        $antennaId = $this->firstId('AntennaTypes');
        $antennaTypes->updateAll(['radio_unit_band_id' => null], ['id' => $antennaId]);
        $this->getTableLocator()->get('RadioUnits')->updateAll(['antenna_type_id' => $antennaId], ['id' => $radioUnitId]);
        $other = $this->addBand();
        $antennaTypes->updateAll(['radio_unit_band_id' => $other], ['id' => $antennaId]);

        $this->get('/radio-units/edit/' . $radioUnitId);

        $this->assertResponseOk();
        $this->assertArrayHasKey($antennaId, $this->viewVariable('antennaTypes')->toArray());
    }

    /**
     * Refreshing the form after picking another type offers what fits the new type.
     *
     * @return void
     * @link \App\Controller\RadioUnitsController::edit()
     */
    public function testRefreshingTheFormReadsTheTypePicked(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $radioUnitId = $this->firstId('RadioUnits');
        $picked = $this->getTableLocator()->get('RadioUnitTypes')->saveOrFail(
            $this->getTableLocator()->get('RadioUnitTypes')->newEntity(
                ['name' => 'Another type', 'radio_unit_band_id' => $this->addBand()],
                ['validate' => false],
            ),
            ['checkRules' => false],
        );

        $this->post('/radio-units/edit/' . $radioUnitId, [
            'refresh' => 'refresh',
            'radio_unit_type_id' => $picked->id,
        ]);

        $this->assertResponseOk();
        $this->assertSame($picked->id, $this->viewVariable('radioUnit')->radio_unit_type_id);
    }

    /**
     * A type of no band offers the antennas of no band, it does not fall over.
     *
     * @return void
     * @link \App\Controller\RadioUnitsController::edit()
     */
    public function testATypeOfNoBandStillDrawsTheForm(): void
    {
        $this->login();
        $radioUnitId = $this->firstId('RadioUnits');
        $typeId = $this->getTableLocator()->get('RadioUnits')->get($radioUnitId)->radio_unit_type_id;
        $this->getTableLocator()->get('RadioUnitTypes')->updateAll(['radio_unit_band_id' => null], ['id' => $typeId]);

        $this->get('/radio-units/edit/' . $radioUnitId);

        $this->assertResponseOk();
    }

    /**
     * The names of the units on the export just requested.
     *
     * @return list<string|null>
     */
    private function exported(): array
    {
        /** @var iterable<\App\Model\Entity\RadioUnit> $radioUnits */
        $radioUnits = $this->viewVariable('radioUnits');

        $names = [];
        foreach ($radioUnits as $radioUnit) {
            $names[] = $radioUnit->name;
        }

        return $names;
    }

    /**
     * Adds a band nothing else belongs to.
     *
     * @return string
     */
    private function addBand(): string
    {
        $bands = $this->getTableLocator()->get('RadioUnitBands');

        return (string)$bands->saveOrFail(
            $bands->newEntity(['name' => '17 GHz'], ['validate' => false]),
            ['checkRules' => false],
        )->id;
    }
}
