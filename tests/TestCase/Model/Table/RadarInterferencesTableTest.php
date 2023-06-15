<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\RadarInterferencesTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

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
     * @var array
     */
    protected array $fixtures = [
        'app.RadarInterferences',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
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
    public function tearDown(): void
    {
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
