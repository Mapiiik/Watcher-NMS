<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\PowerSuppliesTable;
use App\Test\Traits\TableTestTrait;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Override;

/**
 * App\Model\Table\PowerSuppliesTable Test Case
 */
class PowerSuppliesTableTest extends TestCase
{
    use TableTestTrait;

    /**
     * Test subject
     *
     * @var \App\Model\Table\PowerSuppliesTable
     */
    protected $PowerSupplies;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.AccessPointTypes',
        'app.AccessPoints',
        'app.Manufacturers',
        'app.PowerSupplyTypes',
        'app.PowerSupplies',
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
        $config = TableRegistry::getTableLocator()->exists('PowerSupplies') ? [] : ['className' => PowerSuppliesTable::class];
        $this->PowerSupplies = TableRegistry::getTableLocator()->get('PowerSupplies', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    #[Override]
    protected function tearDown(): void
    {
        unset($this->PowerSupplies);

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
        $this->assertAssociationsMatchTheSchema($this->PowerSupplies);
    }

    /**
     * Every column may stay empty here, so what can go wrong is the validator itself - see the
     * trait.
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        $this->assertValidatorIsNotEmpty($this->PowerSupplies);
    }

    /**
     * The address a power supply is pinged at is the address of the device or nothing, never a
     * network, whose first address would be pinged instead.
     *
     * @return void
     * @link \App\Model\Table\PowerSuppliesTable::validationDefault()
     */
    public function testTheIpAddressIsAnIpAddressOrNothing(): void
    {
        foreach (['10.0.0.202', '10.0.0.202/32', '2001:db8::202', '2001:db8::202/128', ''] as $ipAddress) {
            $powerSupply = $this->PowerSupplies->newEntity(['ip_address' => $ipAddress]);
            $this->assertSame([], $powerSupply->getError('ip_address'), $ipAddress);
        }

        foreach (['power supply', '10.0.0.0/24', '10.0.0.202/30', '2001:db8::/64'] as $ipAddress) {
            $powerSupply = $this->PowerSupplies->newEntity(['ip_address' => $ipAddress]);
            $this->assertNotSame([], $powerSupply->getError('ip_address'), $ipAddress);
        }
    }

    /**
     * The rules refuse a record whose references point nowhere - see the trait for why that is
     * the question worth asking here.
     *
     * @return void
     */
    public function testBuildRules(): void
    {
        $this->assertDanglingReferencesAreRefused($this->PowerSupplies);
    }
}
