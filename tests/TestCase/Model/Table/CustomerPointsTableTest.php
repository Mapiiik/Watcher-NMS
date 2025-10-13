<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\CustomerPointsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Override;

/**
 * App\Model\Table\CustomerPointsTable Test Case
 */
class CustomerPointsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\CustomerPointsTable
     */
    protected $CustomerPoints;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.AppUsers',
        'app.CustomerPoints',
        'app.CustomerConnections',
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
        $config = TableRegistry::getTableLocator()->exists('CustomerPoints') ? [] : ['className' => CustomerPointsTable::class];
        $this->CustomerPoints = TableRegistry::getTableLocator()->get('CustomerPoints', $config);
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
        unset($this->CustomerPoints);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
