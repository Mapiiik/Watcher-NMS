<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\RadarInterferencesTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Override;

/**
 * App\Model\Table\RadarInterferencesTable Test Case
 */
class RadarInterferencesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\RadarInterferencesTable
     */
    protected $RadarInterferences;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.AppUsers',
        'app.RadarInterferences',
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
        $config = TableRegistry::getTableLocator()->exists('RadarInterferences') ? [] : ['className' => RadarInterferencesTable::class];
        $this->RadarInterferences = TableRegistry::getTableLocator()->get('RadarInterferences', $config);
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
        unset($this->RadarInterferences);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
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
