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
}
