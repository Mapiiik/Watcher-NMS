<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\RouterosDeviceInterfacesTable;
use App\Test\Traits\TableTestTrait;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Override;

/**
 * App\Model\Table\RouterosDeviceInterfacesTable Test Case
 */
class RouterosDeviceInterfacesTableTest extends TestCase
{
    use TableTestTrait;

    /**
     * Test subject
     *
     * @var \App\Model\Table\RouterosDeviceInterfacesTable
     */
    protected $RouterosDeviceInterfaces;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.AccessPointTypes',
        'app.AccessPoints',
        'app.CustomerPoints',
        'app.CustomerConnections',
        'app.DeviceTypes',
        'app.RouterosDevices',
        'app.RouterosDeviceInterfaces',
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
        $config = TableRegistry::getTableLocator()->exists('RouterosDeviceInterfaces') ? [] : ['className' => RouterosDeviceInterfacesTable::class];
        $this->RouterosDeviceInterfaces = TableRegistry::getTableLocator()->get('RouterosDeviceInterfaces', $config);
    }

    /**
     * What an interface measures moves with every reading and stays out of the audit log, while
     * what somebody set is what the log is there for.
     *
     * @return void
     */
    public function testOnlyWhatSomebodySetIsAudited(): void
    {
        $interfaces = $this->RouterosDeviceInterfaces;
        $connection = $interfaces->getConnection();
        $connection->execute('DELETE FROM audit_logs');

        $logged = fn(): int => (int)$connection
            ->selectQuery(['c' => 'count(*)'], 'audit_logs')
            ->execute()
            ->fetchAll('assoc')[0]['c'];

        $interface = $interfaces->find()->firstOrFail();

        $interfaces->saveOrFail($interfaces->patchEntity($interface, [
            'noise_floor' => -104,
            'client_count' => 17,
            'overall_tx_ccq' => 86,
        ]));
        $this->assertSame(0, $logged(), 'A reading is not a change anybody made.');

        $interfaces->saveOrFail($interfaces->patchEntity($interface, ['ssid' => 'Another network']));
        $this->assertSame(1, $logged());
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
        unset($this->RouterosDeviceInterfaces);

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
        $this->assertEmptyRecordIsRefused($this->RouterosDeviceInterfaces);
    }

    /**
     * The rules refuse a record whose references point nowhere - see the trait for why that is
     * the question worth asking here.
     *
     * @return void
     */
    public function testBuildRules(): void
    {
        $this->assertDanglingReferencesAreRefused($this->RouterosDeviceInterfaces);
    }
}
