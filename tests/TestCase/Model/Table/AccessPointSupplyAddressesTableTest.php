<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\AccessPointSupplyAddressesTable;
use App\Test\Traits\TableTestTrait;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Override;

/**
 * App\Model\Table\AccessPointSupplyAddressesTable Test Case
 */
class AccessPointSupplyAddressesTableTest extends TestCase
{
    use TableTestTrait;

    /**
     * Test subject
     *
     * @var \App\Model\Table\AccessPointSupplyAddressesTable
     */
    protected $AccessPointSupplyAddresses;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.AppUsers',
        'app.AccessPointTypes',
        'app.AccessPoints',
        'app.AccessPointSupplyAddresses',
        'app.PowerOutages',
        'app.PowerOutageScopes',
        'app.AccessPointPowerOutages',
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
        $config = TableRegistry::getTableLocator()->exists('AccessPointSupplyAddresses') ? [] : ['className' => AccessPointSupplyAddressesTable::class];
        $this->AccessPointSupplyAddresses = TableRegistry::getTableLocator()->get('AccessPointSupplyAddresses', $config);
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
        unset($this->AccessPointSupplyAddresses);

        parent::tearDown();
    }

    /**
     * Every association names a column that is really there - see the trait for why that is the
     * question worth asking here.
     *
     * @return void
     */
    public function testInitialize(): void
    {
        $this->assertAssociationsMatchTheSchema($this->AccessPointSupplyAddresses);
    }

    /**
     * A new record with nothing filled in is refused - see the trait for why that is the question
     * worth asking here.
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        $this->assertEmptyRecordIsRefused($this->AccessPointSupplyAddresses);
    }

    /**
     * The rules refuse a record whose references point nowhere - see the trait for why that is
     * the question worth asking here.
     *
     * @return void
     */
    public function testBuildRules(): void
    {
        $this->assertDanglingReferencesAreRefused($this->AccessPointSupplyAddresses);
    }
}
