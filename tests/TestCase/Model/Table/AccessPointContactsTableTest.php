<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\AccessPointContactsTable;
use App\Test\Traits\ConfigureTestTrait;
use App\Test\Traits\TableTestTrait;
use Cake\TestSuite\TestCase;
use Override;

/**
 * App\Model\Table\AccessPointContactsTable Test Case
 */
class AccessPointContactsTableTest extends TestCase
{
    use ConfigureTestTrait;
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

        $this->restoreConfigure();

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

    /**
     * Numbers are kept in one format whichever way they were typed in, so that a contact reached
     * from abroad is dialled the same as one reached from the office.
     *
     * @return void
     * @link \App\Model\Table\AccessPointContactsTable::beforeMarshal()
     */
    public function testANumberIsStoredInTheInternationalFormat(): void
    {
        $this->withConfigure(['Phones.defaultRegion' => 'CZ']);

        $contact = $this->AccessPointContacts->newEntity(['phone' => '601234567']);

        $this->assertSame('+420 601 234 567', $contact->phone);
    }

    /**
     * A value that cannot be read as a number is left as it was typed - the rule below is what
     * reports it, rather than the field quietly changing under the hand that filled it in.
     *
     * @return void
     * @link \App\Model\Table\AccessPointContactsTable::beforeMarshal()
     */
    public function testAValueThatIsNotANumberIsLeftAsItWasTyped(): void
    {
        $contact = $this->AccessPointContacts->newEntity(['phone' => 'reception desk']);

        $this->assertSame('reception desk', $contact->phone);
    }

    /**
     * A contact carrying a number nobody can dial is refused, so that the number is noticed while
     * somebody is still looking at the form.
     *
     * @return void
     * @link \App\Model\Table\AccessPointContactsTable::buildRules()
     */
    public function testAContactWithANumberNobodyCanDialIsRefused(): void
    {
        $contact = $this->AccessPointContacts->newEntity([
            'name' => 'Reception',
            'phone' => 'reception desk',
        ]);

        $this->assertFalse($this->AccessPointContacts->save($contact));
        $this->assertArrayHasKey('phone', $contact->getErrors());
    }

    /**
     * A contact does not have to carry a phone at all - plenty are reached by mail alone.
     *
     * @return void
     * @link \App\Model\Table\AccessPointContactsTable::buildRules()
     */
    public function testAContactWithoutAPhoneIsAccepted(): void
    {
        $contact = $this->AccessPointContacts->newEntity([
            'name' => 'Reception',
            'email' => 'reception@example.com',
        ]);

        $this->assertNotFalse($this->AccessPointContacts->save($contact));
    }
}
