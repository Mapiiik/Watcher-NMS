<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Entity\AccessPoint;
use App\Model\Table\AccessPointsTable;
use Cake\I18n\DateTime;
use Cake\TestSuite\TestCase;
use Override;

/**
 * App\Model\Table\AccessPointsTable Test Case
 */
class AccessPointsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\AccessPointsTable
     */
    protected $AccessPoints;

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
     * setUp method
     *
     * @return void
     */
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('AccessPoints') ? [] : ['className' => AccessPointsTable::class];
        $this->AccessPoints = $this->getTableLocator()->get('AccessPoints', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    #[Override]
    protected function tearDown(): void
    {
        /** @phpstan-ignore unset.possiblyHookedProperty */
        unset($this->AccessPoints);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\AccessPointsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\AccessPointsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getAncestors method
     *
     * @return void
     * @link \App\Model\Table\AccessPointsTable::getAncestors()
     */
    public function testGetAncestors(): void
    {
        $tree = $this->createTree();

        $ancestors = $this->AccessPoints->getAncestors($tree['leaf']->id);

        $this->assertSame(
            [$tree['root']->id, $tree['branch']->id],
            array_map(fn(AccessPoint $accessPoint): string => $accessPoint->id, $ancestors),
        );
        $this->assertSame(
            [2, 1],
            array_map(fn(AccessPoint $accessPoint): int => $accessPoint->tree_depth, $ancestors),
        );
    }

    /**
     * Test getAncestors method for an access point without a parent
     *
     * @return void
     * @link \App\Model\Table\AccessPointsTable::getAncestors()
     */
    public function testGetAncestorsOfRoot(): void
    {
        $tree = $this->createTree();

        $this->assertSame([], $this->AccessPoints->getAncestors($tree['root']->id));
    }

    /**
     * Test getAncestors method for parent references forming a cycle
     *
     * @return void
     * @link \App\Model\Table\AccessPointsTable::getAncestors()
     */
    public function testGetAncestorsOfCycle(): void
    {
        $tree = $this->createTree();
        $tree['root']->parent_access_point_id = $tree['leaf']->id;
        $this->AccessPoints->saveOrFail($tree['root']);

        // The walk stops at the root instead of running into the leaf it started from again.
        $this->assertSame(
            [$tree['root']->id, $tree['branch']->id],
            array_map(
                fn(AccessPoint $accessPoint): string => $accessPoint->id,
                $this->AccessPoints->getAncestors($tree['leaf']->id),
            ),
        );
    }

    /**
     * Test getSubtree method
     *
     * @return void
     * @link \App\Model\Table\AccessPointsTable::getSubtree()
     */
    public function testGetSubtree(): void
    {
        $tree = $this->createTree();

        $subtree = $this->AccessPoints->getSubtree($tree['root']->id);

        // Descendants directly follow their parent, siblings are ordered by name.
        $this->assertSame(
            [$tree['root']->id, $tree['branch']->id, $tree['leaf']->id, $tree['sibling']->id],
            array_map(fn(AccessPoint $accessPoint): string => $accessPoint->id, $subtree),
        );
        $this->assertSame(
            [0, 1, 2, 1],
            array_map(fn(AccessPoint $accessPoint): int => $accessPoint->tree_depth, $subtree),
        );
        // The archived customer connection of the leaf is not counted.
        $this->assertSame(
            [0, 1, 2, 0],
            array_map(
                fn(AccessPoint $accessPoint): int => $accessPoint->customer_connections_count,
                $subtree,
            ),
        );
        $this->assertSame(
            [3, 3, 2, 0],
            array_map(
                fn(AccessPoint $accessPoint): int => $accessPoint->subtree_customer_connections_count,
                $subtree,
            ),
        );
    }

    /**
     * Test getSubtree method for an access point without descendants
     *
     * @return void
     * @link \App\Model\Table\AccessPointsTable::getSubtree()
     */
    public function testGetSubtreeOfLeaf(): void
    {
        $tree = $this->createTree();

        $subtree = $this->AccessPoints->getSubtree($tree['sibling']->id);

        $this->assertCount(1, $subtree);
        $this->assertSame($tree['sibling']->id, $subtree[0]->id);
        $this->assertSame(0, $subtree[0]->tree_depth);
    }

    /**
     * Test getSubtree method for parent references forming a cycle
     *
     * @return void
     * @link \App\Model\Table\AccessPointsTable::getSubtree()
     */
    public function testGetSubtreeOfCycle(): void
    {
        $tree = $this->createTree();
        $tree['root']->parent_access_point_id = $tree['leaf']->id;
        $this->AccessPoints->saveOrFail($tree['root']);

        $this->assertSame(
            [$tree['branch']->id, $tree['leaf']->id, $tree['root']->id, $tree['sibling']->id],
            array_map(
                fn(AccessPoint $accessPoint): string => $accessPoint->id,
                $this->AccessPoints->getSubtree($tree['branch']->id),
            ),
        );
    }

    /**
     * Test getSubtree method without an access point to start at
     *
     * @return void
     * @link \App\Model\Table\AccessPointsTable::getSubtree()
     */
    public function testGetSubtreeOfAllRoots(): void
    {
        $tree = $this->createTree();

        $subtree = $this->AccessPoints->getSubtree();

        // Every access point is listed, the subtrees ordered by the name of their root.
        $this->assertSame(
            [
                'Lorem ipsum dolor sit',
                '- Lorem ipsum dolor sit amet',
                'Tree root',
                '- A branch',
                '- - Leaf',
                '- B sibling',
            ],
            $this->describeSubtree($subtree),
        );
        $this->assertSame(
            [$tree['root']->id, $tree['branch']->id, $tree['leaf']->id, $tree['sibling']->id],
            array_map(
                fn(AccessPoint $accessPoint): string => $accessPoint->id,
                array_slice($subtree, 2),
            ),
        );
        // The counts of a subtree are the ones it carries on its own.
        $this->assertSame(
            [0, 0, 3, 3, 2, 0],
            array_map(
                fn(AccessPoint $accessPoint): int => $accessPoint->subtree_customer_connections_count,
                $subtree,
            ),
        );
    }

    /**
     * Test getSubtree method without an access point to start at for parent references forming a cycle
     *
     * @return void
     * @link \App\Model\Table\AccessPointsTable::getSubtree()
     */
    public function testGetSubtreeOfAllRootsWithCycle(): void
    {
        $tree = $this->createTree();
        $tree['root']->parent_access_point_id = $tree['leaf']->id;
        $this->AccessPoints->saveOrFail($tree['root']);

        // None of the four is reachable from an access point without a parent any more,
        // so each of them starts a subtree of its own instead of being left out.
        $this->assertSame(
            [
                'A branch',
                'B sibling',
                'Leaf',
                'Lorem ipsum dolor sit',
                '- Lorem ipsum dolor sit amet',
                'Tree root',
            ],
            $this->describeSubtree($this->AccessPoints->getSubtree()),
        );
    }

    /**
     * Renders a subtree as one name per access point, prefixed by its depth.
     *
     * @param array<\App\Model\Entity\AccessPoint> $subtree Access points ordered depth first.
     * @return array<string>
     */
    private function describeSubtree(array $subtree): array
    {
        return array_map(
            fn(AccessPoint $accessPoint): string => str_repeat('- ', $accessPoint->tree_depth) . $accessPoint->name,
            $subtree,
        );
    }

    /**
     * Creates an access point tree to run the tree finders against.
     *
     * The tree is `root` -> (`A branch` -> `Leaf`, `B sibling`), where the branch holds one
     * customer connection and the leaf two active ones plus an archived one.
     *
     * @return array<string, \App\Model\Entity\AccessPoint>
     */
    private function createTree(): array
    {
        $root = $this->createAccessPoint('Tree root', null);
        $branch = $this->createAccessPoint('A branch', $root->id);
        $leaf = $this->createAccessPoint('Leaf', $branch->id);
        $sibling = $this->createAccessPoint('B sibling', $root->id);

        $this->createCustomerConnection($branch->id);
        $this->createCustomerConnection($leaf->id);
        $this->createCustomerConnection($leaf->id);
        $this->createCustomerConnection($leaf->id, DateTime::now());

        return compact('root', 'branch', 'leaf', 'sibling');
    }

    /**
     * Creates a single access point.
     *
     * @param string $name Name of the access point.
     * @param string|null $parentId Id of the parent access point.
     * @return \App\Model\Entity\AccessPoint
     */
    private function createAccessPoint(string $name, ?string $parentId): AccessPoint
    {
        return $this->AccessPoints->saveOrFail($this->AccessPoints->newEntity([
            'name' => $name,
            'parent_access_point_id' => $parentId,
        ]));
    }

    /**
     * Creates a single customer connection.
     *
     * @param string $accessPointId Id of the access point to connect to.
     * @param \Cake\I18n\DateTime|null $archived Timestamp making the connection archived.
     * @return void
     */
    private function createCustomerConnection(string $accessPointId, ?DateTime $archived = null): void
    {
        $customerConnections = $this->AccessPoints->CustomerConnections;
        $customerConnections->saveOrFail($customerConnections->newEntity([
            'name' => 'Customer connection',
            'access_point_id' => $accessPointId,
            'archived' => $archived,
        ]));
    }
}
