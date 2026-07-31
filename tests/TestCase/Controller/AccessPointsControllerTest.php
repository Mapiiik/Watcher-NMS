<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\AccessPointsController;
use Cake\Core\Configure;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Controller\AccessPointsController Test Case
 */
#[UsesClass(AccessPointsController::class)]
class AccessPointsControllerTest extends TestCase
{
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
     * Test index method
     *
     * @return void
     */
    public function testIndex(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
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
     * login method
     *
     * @return void
     */
    protected function login(): void
    {
        /** @var \App\Model\Table\AppUsersTable $usersTable */
        $usersTable = $this->getTableLocator()->get(Configure::read('Users.table', 'Users'));

        $user = $usersTable->newEmptyEntity();
        $user->username = 'tester';
        $user->role = 'admin';
        $user->active = true;

        $this->session(['Auth' => $user]);
    }

    /**
     * Test add method
     *
     * @return void
     */
    public function testAdd(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test edit method
     *
     * @return void
     */
    public function testEdit(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test delete method
     *
     * @return void
     */
    public function testDelete(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test map method
     *
     * @return void
     */
    public function testMap(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
