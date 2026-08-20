<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Entity\AccessPoint;
use App\Model\Table\AccessPointsTable;
use App\Test\Traits\TableTestTrait;
use Cake\I18n\DateTime;
use Cake\TestSuite\TestCase;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * App\Model\Table\AccessPointsTable Test Case
 */
class AccessPointsTableTest extends TestCase
{
    use TableTestTrait;

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
        'app.AccessPointSupplyAddresses',
        'app.PowerOutages',
        'app.PowerOutageScopes',
        'app.AccessPointPowerOutages',
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
     * A new record with nothing filled in is refused - see the trait for why that is the question
     * worth asking here.
     *
     * @return void
     * @link \App\Model\Table\AccessPointsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->assertEmptyRecordIsRefused($this->AccessPoints);
    }

    /**
     * The rules refuse a record whose references point nowhere - see the trait for why that is
     * the question worth asking here.
     *
     * @return void
     * @link \App\Model\Table\AccessPointsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->assertDanglingReferencesAreRefused($this->AccessPoints);
    }

    /**
     * The supply point is judged by its shape, check digit and all.
     *
     * Worth asking of every branch rather than only of the happy one: a mistyped supply point is
     * never the subject of an outage, which on the screen looks exactly like having none - so this
     * is the one validation here whose absence would be invisible.
     *
     * @return void
     * @link \App\Model\Table\AccessPointsTable::isValidEan()
     */
    #[DataProvider('eansAndWhetherTheyAreValid')]
    public function testIsValidEan(mixed $ean, bool $expected): void
    {
        $this->assertSame($expected, AccessPointsTable::isValidEan($ean));
    }

    /**
     * @return array<string, array{mixed, bool}>
     */
    public static function eansAndWhetherTheyAreValid(): array
    {
        return [
            'eighteen digits with the right check digit' => ['859182400000001231', true],
            'the same with the check digit one out' => ['859182400000001232', false],
            'two digits transposed' => ['859182400000002131', false],
            'seventeen digits' => ['85918240000000123', false],
            'nineteen digits' => ['8591824000000012310', false],
            'letters in it' => ['85918240000000123X', false],
            'spaces in it' => ['859182 400000001231', false],
            'not a string at all' => [859182400000001231, false],
        ];
    }

    /**
     * An access point may have no supply point, which is the ordinary case until somebody looks
     * one up.
     *
     * @return void
     * @link \App\Model\Table\AccessPointsTable::validationDefault()
     */
    public function testValidationDefaultAllowsNoEan(): void
    {
        $accessPoint = $this->AccessPoints->newEntity(['name' => 'No supply point', 'electricity_ean' => '']);

        $this->assertArrayNotHasKey('electricity_ean', $accessPoint->getErrors());
    }

    /**
     * A supply point that could not be one is refused rather than stored to be puzzled over.
     *
     * @return void
     * @link \App\Model\Table\AccessPointsTable::validationDefault()
     */
    public function testValidationDefaultRefusesAMistypedEan(): void
    {
        $accessPoint = $this->AccessPoints->newEntity([
            'name' => 'Mistyped supply point',
            'electricity_ean' => '859182400000001232',
        ]);

        $this->assertArrayHasKey('electricity_ean', $accessPoint->getErrors());
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
                'Kolin water tower',
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
                array_slice($subtree, 3),
            ),
        );
        // The counts of a subtree are the ones it carries on its own.
        $this->assertSame(
            [0, 0, 0, 3, 3, 2, 0],
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
                'Kolin water tower',
                'Leaf',
                'Lorem ipsum dolor sit',
                '- Lorem ipsum dolor sit amet',
                'Tree root',
            ],
            $this->describeSubtree($this->AccessPoints->getSubtree()),
        );
    }

    /**
     * Test filterSubtree method without a threshold
     *
     * @return void
     * @link \App\Model\Table\AccessPointsTable::filterSubtree()
     */
    public function testFilterSubtreeWithoutThresholds(): void
    {
        $this->createTree();

        $subtree = $this->AccessPoints->getSubtree();

        $this->assertSame($subtree, $this->AccessPoints->filterSubtree($subtree));
    }

    /**
     * Test filterSubtree method for the customer connections of the access points themselves
     *
     * @return void
     * @link \App\Model\Table\AccessPointsTable::filterSubtree()
     */
    public function testFilterSubtreeByCustomerConnections(): void
    {
        $this->createTree();

        $filtered = $this->AccessPoints->filterSubtree(
            $this->AccessPoints->getSubtree(),
            minCustomerConnections: 2,
        );

        // Only the leaf carries two connections of its own. The access points above it are the
        // path leading down to it, the ones carrying nothing below it are gone.
        $this->assertSame(
            ['Tree root', '- A branch', '- - Leaf'],
            $this->describeSubtree($filtered),
        );
    }

    /**
     * Test filterSubtree method for the customer connections of whole subtrees
     *
     * @return void
     * @link \App\Model\Table\AccessPointsTable::filterSubtree()
     */
    public function testFilterSubtreeBySubtreeCustomerConnections(): void
    {
        $this->createTree();

        $filtered = $this->AccessPoints->filterSubtree(
            $this->AccessPoints->getSubtree(),
            minSubtreeCustomerConnections: 3,
        );

        // The leaf carries two connections and the sibling none, so both subtrees are cut off.
        $this->assertSame(
            ['Tree root', '- A branch'],
            $this->describeSubtree($filtered),
        );
    }

    /**
     * Test filterSubtree method for both thresholds at once
     *
     * @return void
     * @link \App\Model\Table\AccessPointsTable::filterSubtree()
     */
    public function testFilterSubtreeByBothThresholds(): void
    {
        $this->createTree();

        $filtered = $this->AccessPoints->filterSubtree(
            $this->AccessPoints->getSubtree(),
            minCustomerConnections: 1,
            minSubtreeCustomerConnections: 3,
        );

        // The leaf carries connections enough of its own, but its subtree holds only two of them.
        $this->assertSame(
            ['Tree root', '- A branch'],
            $this->describeSubtree($filtered),
        );
    }

    /**
     * Test filterSubtree method for an upper threshold
     *
     * @return void
     * @link \App\Model\Table\AccessPointsTable::filterSubtree()
     */
    public function testFilterSubtreeByMaximumCustomerConnections(): void
    {
        $this->createTree();

        $filtered = $this->AccessPoints->filterSubtree(
            $this->AccessPoints->getSubtree(),
            maxCustomerConnections: 0,
        );

        // The branch and the leaf carry customers, so nothing of that side of the tree is left.
        $this->assertSame(
            [
                'Kolin water tower',
                'Lorem ipsum dolor sit',
                '- Lorem ipsum dolor sit amet',
                'Tree root',
                '- B sibling',
            ],
            $this->describeSubtree($filtered),
        );
    }

    /**
     * Test filterSubtree method marking the access points that meet the thresholds
     *
     * @return void
     * @link \App\Model\Table\AccessPointsTable::filterSubtree()
     */
    public function testFilterSubtreeMarksTheAccessPointsFound(): void
    {
        $this->createTree();

        $filtered = $this->AccessPoints->filterSubtree(
            $this->AccessPoints->getSubtree(),
            minCustomerConnections: 1,
            maxCustomerConnections: 1,
        );

        // The root carries nothing itself and is only there as the path down to the branch.
        $this->assertSame(['Tree root', '- A branch'], $this->describeSubtree($filtered));
        $this->assertFalse($filtered[0]->matches_thresholds);
        $this->assertTrue($filtered[1]->matches_thresholds);
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
