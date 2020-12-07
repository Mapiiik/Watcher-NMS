<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\AccessPointContactsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\AccessPointContactsTable Test Case
 */
class AccessPointContactsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\AccessPointContactsTable
     */
    protected $AccessPointContacts;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'app.AccessPointContacts',
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
        $config = $this->getTableLocator()->exists('AccessPointContacts') ? [] : ['className' => AccessPointContactsTable::class];
        $this->AccessPointContacts = $this->getTableLocator()->get('AccessPointContacts', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->AccessPointContacts);

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
