<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\DeviceTypesTable;
use Cake\TestSuite\TestCase;
use Override;

/**
 * App\Model\Table\DeviceTypesTable Test Case
 */
class DeviceTypesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\DeviceTypesTable
     */
    protected $DeviceTypes;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.AppUsers',
        'app.DeviceTypes',
        'app.AccessPointTypes',
        'app.AccessPoints',
        'app.CustomerPoints',
        'app.CustomerConnections',
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
        $config = $this->getTableLocator()->exists('DeviceTypes') ? [] : ['className' => DeviceTypesTable::class];
        $this->DeviceTypes = $this->getTableLocator()->get('DeviceTypes', $config);
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
        unset($this->DeviceTypes);

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
