<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\RadioUnitTypesController;
use App\Test\Traits\ControllerTestTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Controller\RadioUnitTypesController Test Case
 *
 * Smoke tests: every action is requested once and has to answer. They are deliberately shallow -
 * their job is to notice an action that stopped answering at all, which is where the query building
 * bugs turn up.
 */
#[UsesClass(RadioUnitTypesController::class)]
class RadioUnitTypesControllerTest extends TestCase
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
     * @link \App\Controller\RadioUnitTypesController::index()
     */
    public function testIndex(): void
    {
        $this->login();
        $this->get('/radio-unit-types');

        $this->assertResponseOk();
    }

    /**
     * The listing renders with the search filled in, which builds a different query than the plain
     * listing does and is therefore worth requesting on its own.
     *
     * @return void
     * @link \App\Controller\RadioUnitTypesController::index()
     */
    public function testIndexWithSearch(): void
    {
        $this->login();
        $this->get('/radio-unit-types?search=Lorem');

        $this->assertResponseOk();
    }

    /**
     * The detail of a record renders.
     *
     * @return void
     * @link \App\Controller\RadioUnitTypesController::view()
     */
    public function testView(): void
    {
        $this->login();
        $this->get('/radio-unit-types/view/' . $this->firstId('RadioUnitTypes'));

        $this->assertResponseOk();
    }

    /**
     * The form for a new record renders.
     *
     * @return void
     * @link \App\Controller\RadioUnitTypesController::add()
     */
    public function testAdd(): void
    {
        $this->login();
        $this->get('/radio-unit-types/add');

        $this->assertResponseOk();
    }

    /**
     * The form of an existing record renders.
     *
     * @return void
     * @link \App\Controller\RadioUnitTypesController::edit()
     */
    public function testEdit(): void
    {
        $this->login();
        $this->get('/radio-unit-types/edit/' . $this->firstId('RadioUnitTypes'));

        $this->assertResponseOk();
    }

    /**
     * The delete action runs and redirects. Whether the record really goes depends on what else
     * still references it, which is the application rules' business rather than this test's.
     *
     * @return void
     * @link \App\Controller\RadioUnitTypesController::delete()
     */
    public function testDelete(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/radio-unit-types/delete/' . $this->firstId('RadioUnitTypes'));

        $this->assertRedirect();
    }
}
