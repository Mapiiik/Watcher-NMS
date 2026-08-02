<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\AccessPointsController;
use App\Test\Traits\ControllerTestTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Controller\AccessPointsController Test Case
 */
#[UsesClass(AccessPointsController::class)]
class AccessPointsControllerTest extends TestCase
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
        'app.AccessPointTypes',
        'app.AccessPoints',
        'app.AccessPointContacts',
        'app.Manufacturers',
        'app.PowerSupplyTypes',
        'app.PowerSupplies',
        'app.RadioLinks',
        'app.RadioUnitBands',
        'app.RadioUnitTypes',
        'app.AntennaTypes',
        'app.RadioUnits',
        'app.CustomerPoints',
        'app.CustomerConnections',
        'app.DeviceTypes',
        'app.RouterosDevices',
    ];

    /**
     * The listing renders, plain and with the search filled in - the search builds a different
     * query than the plain listing does.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::index()
     */
    public function testIndex(): void
    {
        $this->login();
        $this->get('/access-points');

        $this->assertResponseOk();

        $this->get('/access-points?search=Lorem');

        $this->assertResponseOk();
    }

    /**
     * Test view method
     *
     * @return void
     */
    public function testView(): void
    {
        $this->login();

        $accessPoints = $this->getTableLocator()->get('AccessPoints');
        $root = $accessPoints->saveOrFail($accessPoints->newEntity(['name' => 'Tree root']));
        $child = $accessPoints->saveOrFail($accessPoints->newEntity([
            'name' => 'Tree child',
            'parent_access_point_id' => $root->id,
        ]));

        $this->get('/access-points/view/' . $root->id);

        $this->assertResponseOk();
        // The heading is translated, so it is looked up rather than hard coded.
        $this->assertResponseContains(__('Subordinate Access Points'));
        $this->assertResponseContains('<a href="/access-points/' . $child->id . '">Tree child</a>');

        $this->get('/access-points/view/' . $child->id);

        $this->assertResponseOk();
        // The path leads from the root down to the access point itself.
        $this->assertResponseContains('Tree root</a> &gt; Tree child');
    }

    /**
     * Test utilization method
     *
     * @return void
     */
    public function testUtilization(): void
    {
        $this->login();

        $accessPoints = $this->getTableLocator()->get('AccessPoints');
        $root = $accessPoints->saveOrFail($accessPoints->newEntity(['name' => 'Tree root']));
        $child = $accessPoints->saveOrFail($accessPoints->newEntity([
            'name' => 'Tree child',
            'parent_access_point_id' => $root->id,
        ]));

        $this->get('/access-points/utilization');

        $this->assertResponseOk();
        // The heading is translated, so it is looked up rather than hard coded.
        $this->assertResponseContains(__('Access Points Utilization'));
        // Unlike the subtree of a single access point, the roots are listed as links as well.
        $this->assertResponseContains('<a href="/access-points/' . $root->id . '">Tree root</a>');
        $this->assertResponseContains('<a href="/access-points/' . $child->id . '">Tree child</a>');
    }

    /**
     * Test utilization method with a filter
     *
     * @return void
     */
    public function testUtilizationFilter(): void
    {
        $this->login();

        $accessPoints = $this->getTableLocator()->get('AccessPoints');
        $root = $accessPoints->saveOrFail($accessPoints->newEntity(['name' => 'Tree root']));
        $child = $accessPoints->saveOrFail($accessPoints->newEntity([
            'name' => 'Tree child',
            'parent_access_point_id' => $root->id,
        ]));
        $empty = $accessPoints->saveOrFail($accessPoints->newEntity(['name' => 'Empty root']));

        $customerConnections = $accessPoints->CustomerConnections;
        $customerConnections->saveOrFail($customerConnections->newEntity([
            'name' => 'Customer connection',
            'access_point_id' => $child->id,
        ]));

        $this->get('/access-points/utilization?min_customer_connections=1');

        $this->assertResponseOk();
        $this->assertResponseContains('<a href="/access-points/' . $child->id . '">Tree child</a>');
        // The root carries nothing of its own, it is listed as the path down to the child.
        $this->assertResponseContains('<a href="/access-points/' . $root->id . '">Tree root</a>');
        $this->assertResponseNotContains('<a href="/access-points/' . $empty->id . '">Empty root</a>');
    }

    /**
     * The form for a new access point renders.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::add()
     */
    public function testAdd(): void
    {
        $this->login();
        $this->get('/access-points/add');

        $this->assertResponseOk();
    }

    /**
     * The form of an existing access point renders.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::edit()
     */
    public function testEdit(): void
    {
        $this->login();
        $this->get('/access-points/' . $this->firstId('AccessPoints') . '/edit');

        $this->assertResponseOk();
    }

    /**
     * An access point nothing hangs off is deleted and the caller is sent back to the listing.
     *
     * A fresh one is made for it rather than taking a fixture: deleting an access point that still
     * parents another reaches the foreign key and answers 500, which is worth knowing but is not
     * what this test is about.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::delete()
     */
    public function testDelete(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $accessPoints = $this->getTableLocator()->get('AccessPoints');
        $accessPoint = $accessPoints->saveOrFail($accessPoints->newEntity(['name' => 'Leaf to delete']));

        $this->post('/access-points/' . $accessPoint->id . '/delete');

        $this->assertRedirect();
        $this->assertFalse($accessPoints->exists(['id' => $accessPoint->id]));
    }

    /**
     * The map renders.
     *
     * @return void
     * @link \App\Controller\AccessPointsController::map()
     */
    public function testMap(): void
    {
        $this->login();
        $this->get('/access-points/map');

        $this->assertResponseOk();
    }
}
