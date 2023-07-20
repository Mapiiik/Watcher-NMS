<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ElectricityMeterReadingsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ElectricityMeterReadingsTable Test Case
 */
class ElectricityMeterReadingsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ElectricityMeterReadingsTable
     */
    protected $ElectricityMeterReadings;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.ElectricityMeterReadings',
        'app.AccessPoints',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('ElectricityMeterReadings') ? [] : ['className' => ElectricityMeterReadingsTable::class];
        $this->ElectricityMeterReadings = $this->getTableLocator()->get('ElectricityMeterReadings', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->ElectricityMeterReadings);

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

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
