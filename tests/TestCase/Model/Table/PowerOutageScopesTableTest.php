<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\PowerOutageScopesTable;
use App\Test\Traits\TableTestTrait;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Override;

/**
 * App\Model\Table\PowerOutageScopesTable Test Case
 */
class PowerOutageScopesTableTest extends TestCase
{
    use TableTestTrait;

    /**
     * Test subject
     *
     * @var \App\Model\Table\PowerOutageScopesTable
     */
    protected $PowerOutageScopes;

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
        $config = TableRegistry::getTableLocator()->exists('PowerOutageScopes') ? [] : ['className' => PowerOutageScopesTable::class];
        $this->PowerOutageScopes = TableRegistry::getTableLocator()->get('PowerOutageScopes', $config);
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
        unset($this->PowerOutageScopes);

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
        $this->assertAssociationsMatchTheSchema($this->PowerOutageScopes);
    }

    /**
     * A new record with nothing filled in is refused - see the trait for why that is the question
     * worth asking here.
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        $this->assertEmptyRecordIsRefused($this->PowerOutageScopes);
    }

    /**
     * The rules refuse a record whose references point nowhere - see the trait for why that is
     * the question worth asking here.
     *
     * @return void
     */
    public function testBuildRules(): void
    {
        $this->assertDanglingReferencesAreRefused($this->PowerOutageScopes);
    }
}
