<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ManufacturersTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ManufacturersTable Test Case
 */
class ManufacturersTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ManufacturersTable
     */
    protected $Manufacturers;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'app.Manufacturers',
        'app.PowerSupplyTypes',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('Manufacturers') ? [] : ['className' => ManufacturersTable::class];
        $this->Manufacturers = TableRegistry::getTableLocator()->get('Manufacturers', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Manufacturers);

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
