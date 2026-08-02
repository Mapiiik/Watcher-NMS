<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\CustomerConnectionIpsTable;
use App\Test\Traits\TableTestTrait;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Override;

/**
 * App\Model\Table\CustomerConnectionIpsTable Test Case
 */
class CustomerConnectionIpsTableTest extends TestCase
{
    use TableTestTrait;

    /**
     * Test subject
     *
     * @var \App\Model\Table\CustomerConnectionIpsTable
     */
    protected $CustomerConnectionIps;

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
        $config = TableRegistry::getTableLocator()->exists('CustomerConnectionIps') ? [] : ['className' => CustomerConnectionIpsTable::class];
        $this->CustomerConnectionIps = TableRegistry::getTableLocator()->get('CustomerConnectionIps', $config);
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
        unset($this->CustomerConnectionIps);

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
        $this->assertEmptyRecordIsRefused($this->CustomerConnectionIps);
    }

    /**
     * The rules refuse a record whose references point nowhere - see the trait for why that is
     * the question worth asking here.
     *
     * @return void
     */
    public function testBuildRules(): void
    {
        $this->assertDanglingReferencesAreRefused($this->CustomerConnectionIps);
    }
}
