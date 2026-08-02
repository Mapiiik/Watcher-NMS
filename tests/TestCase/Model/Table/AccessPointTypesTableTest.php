<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\AccessPointTypesTable;
use App\Test\Traits\TableTestTrait;
use Cake\TestSuite\TestCase;
use Override;

/**
 * App\Model\Table\AccessPointTypesTable Test Case
 */
class AccessPointTypesTableTest extends TestCase
{
    use TableTestTrait;

    /**
     * Test subject
     *
     * @var \App\Model\Table\AccessPointTypesTable
     */
    protected $AccessPointTypes;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.AppUsers',
        'app.AccessPointTypes',
        'app.AccessPoints',
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
        $config = $this->getTableLocator()->exists('AccessPointTypes') ? [] : ['className' => AccessPointTypesTable::class];
        $this->AccessPointTypes = $this->getTableLocator()->get('AccessPointTypes', $config);
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
        unset($this->AccessPointTypes);

        parent::tearDown();
    }

    /**
     * A new record with nothing filled in is refused - see the trait for why that is the question
     * worth asking here.
     *
     * @return void
     * @link \App\Model\Table\AccessPointTypesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->assertEmptyRecordIsRefused($this->AccessPointTypes);
    }
}
