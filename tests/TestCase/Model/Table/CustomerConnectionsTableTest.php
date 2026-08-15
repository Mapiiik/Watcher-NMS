<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\CustomerConnectionsTable;
use App\Test\Traits\TableTestTrait;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Override;

/**
 * App\Model\Table\CustomerConnectionsTable Test Case
 */
class CustomerConnectionsTableTest extends TestCase
{
    use TableTestTrait;

    /**
     * Test subject
     *
     * @var \App\Model\Table\CustomerConnectionsTable
     */
    protected $CustomerConnections;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.AppUsers',
        'app.CustomerPoints',
        'app.CustomerConnections',
        'app.CustomerConnectionIps',
        'app.AccessPointTypes',
        'app.AccessPoints',
        'app.DeviceTypes',
        'app.RouterosDevices',
        // A radio unit may stand at a customer too, so a connection has to answer for those as
        // well - and a unit brings the whole of what it is made of with it.
        'app.Manufacturers',
        'app.RadioUnitBands',
        'app.AntennaTypes',
        'app.RadioLinks',
        'app.RadioUnitTypes',
        'app.RadioUnits',
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
        $config = TableRegistry::getTableLocator()->exists('CustomerConnections') ? [] : ['className' => CustomerConnectionsTable::class];
        $this->CustomerConnections = TableRegistry::getTableLocator()->get('CustomerConnections', $config);
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
        unset($this->CustomerConnections);

        parent::tearDown();
    }

    /**
     * A new record with nothing filled in is refused - see the trait for why that is the question
     * worth asking here.
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        $this->assertEmptyRecordIsRefused($this->CustomerConnections);
    }

    /**
     * The rules refuse a record whose references point nowhere - see the trait for why that is
     * the question worth asking here.
     *
     * @return void
     */
    public function testBuildRules(): void
    {
        $this->assertDanglingReferencesAreRefused($this->CustomerConnections);
    }
}
