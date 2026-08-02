<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\AccessPointContactsTable;
use App\Test\Traits\TableTestTrait;
use Cake\TestSuite\TestCase;
use Override;

/**
 * App\Model\Table\AccessPointContactsTable Test Case
 */
class AccessPointContactsTableTest extends TestCase
{
    use TableTestTrait;

    /**
     * Test subject
     *
     * @var \App\Model\Table\AccessPointContactsTable
     */
    protected $AccessPointContacts;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.AppUsers',
        'app.AccessPointTypes',
        'app.AccessPoints',
        'app.AccessPointContacts',
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
        $config = $this->getTableLocator()->exists('AccessPointContacts') ? [] : ['className' => AccessPointContactsTable::class];
        $this->AccessPointContacts = $this->getTableLocator()->get('AccessPointContacts', $config);
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
        unset($this->AccessPointContacts);

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
        $this->assertEmptyRecordIsRefused($this->AccessPointContacts);
    }

    /**
     * The rules refuse a record whose references point nowhere - see the trait for why that is
     * the question worth asking here.
     *
     * @return void
     */
    public function testBuildRules(): void
    {
        $this->assertDanglingReferencesAreRefused($this->AccessPointContacts);
    }
}
