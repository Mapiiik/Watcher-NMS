<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\TasksTable;
use App\Test\Traits\ConfigureTestTrait;
use App\Test\Traits\IdentityColumnTrait;
use App\Test\Traits\TableTestTrait;
use Cake\Datasource\EntityInterface;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\EmailTrait;
use Cake\TestSuite\TestCase;
use Override;

/**
 * App\Model\Table\TasksTable Test Case
 */
class TasksTableTest extends TestCase
{
    use ConfigureTestTrait;
    use EmailTrait;
    use IdentityColumnTrait;
    use TableTestTrait;

    /**
     * Test subject
     *
     * @var \App\Model\Table\TasksTable
     */
    protected $Tasks;

    /**
     * The access point the fixtures carry.
     *
     * @var string
     */
    private const ACCESS_POINT_ID = '1bd5e754-e102-46ad-8488-11b1b44bf026';

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.AppUsers',
        'app.AccessPointTypes',
        'app.AccessPoints',
        'app.TaskStates',
        'app.TaskTypes',
        'app.Tasks',
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
        $config = TableRegistry::getTableLocator()->exists('Tasks') ? [] : ['className' => TasksTable::class];
        $this->Tasks = TableRegistry::getTableLocator()->get('Tasks', $config);

        // an email about a task links to it, and a link wants routes; a request and the console
        // both bring their own, a bare test case does not
        $this->loadRoutes();
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
        unset($this->Tasks);

        parent::tearDown();
    }

    /**
     * Every association names a column that is really there - see the trait for why that is the
     * question worth asking here.
     *
     * @return void
     */
    public function testInitialize(): void
    {
        $this->assertAssociationsMatchTheSchema($this->Tasks);
    }

    /**
     * A new record with nothing filled in is refused - see the trait for why that is the question
     * worth asking here.
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        $this->assertEmptyRecordIsRefused($this->Tasks);
    }

    /**
     * The rules refuse a record whose references point nowhere - see the trait for why that is
     * the question worth asking here.
     *
     * @return void
     */
    public function testBuildRules(): void
    {
        $this->assertDanglingReferencesAreRefused($this->Tasks);
    }

    /**
     * A type that insists on an access point refuses a task that names none, and takes the same
     * task once it does.
     *
     * @return void
     * @link \Tasks\Model\Rule\RequiredLinkRule::__invoke()
     */
    public function testATypeDemandingAnAccessPointRefusesATaskWithoutOne(): void
    {
        $type = $this->taskType(['access_point_required' => true]);

        $refused = $this->Tasks->newEntity($this->task($type) + ['access_point_id' => null]);
        $this->assertFalse((bool)$this->Tasks->save($refused));
        $this->assertArrayHasKey('isRequiredAccessPointFilled', $refused->getError('access_point_id'));

        $taken = $this->Tasks->newEntity($this->task($type) + ['access_point_id' => self::ACCESS_POINT_ID]);
        $this->assertNotFalse($this->Tasks->save($taken), 'The link it asked for is there.');
    }

    /**
     * A type that insists on nothing takes a task that names nothing, which is the branch that
     * would quietly refuse everything if the flag were read the wrong way round.
     *
     * @return void
     * @link \Tasks\Model\Rule\RequiredLinkRule::__invoke()
     */
    public function testATypeDemandingNothingTakesATaskWithNoAccessPoint(): void
    {
        $type = $this->taskType(['access_point_required' => false]);

        $task = $this->Tasks->newEntity($this->task($type));
        $this->assertNotFalse($this->Tasks->save($task));
    }

    /**
     * Closing a task of a reporting type tells the report addresses.
     *
     * The decision itself is the plugin's and is asked after there; what this pins down is the
     * half that is this application's - that the task can be read together with the access point
     * it is filed under, and drawn into an email that way.
     *
     * @return void
     * @link \Tasks\Model\Table\TasksTable::afterSaveCommit()
     */
    public function testClosingATaskOfAReportingTypeTellsTheReportAddresses(): void
    {
        $this->withConfigure(['Report.emails' => ['operations@example.com']]);

        $type = $this->taskType(['report_on_completion' => true, 'access_point_required' => true]);
        $task = $this->Tasks->saveOrFail($this->Tasks->newEntity(
            ['access_point_id' => self::ACCESS_POINT_ID] + $this->task($type),
        ));

        $task->set('task_state_id', $this->stateId(completed: true));
        $this->assertNotFalse($this->Tasks->save($task));

        $this->assertMailCount(1);
        $this->assertMailSentTo('operations@example.com');
        $this->assertMailSubjectContains('Task completed');
    }

    /**
     * A task type asking for exactly what it is given.
     *
     * @param array<string, mixed> $flags What this type insists on.
     */
    private function taskType(array $flags): EntityInterface
    {
        $types = $this->getTableLocator()->get('TaskTypes');

        return $types->saveOrFail($types->newEntity($flags + ['name' => 'Written by the test']));
    }

    /**
     * The least a task needs to be saved at all, so that what refuses it is the rule under test.
     *
     * @param \Cake\Datasource\EntityInterface $type The type it is filed under.
     * @return array<string, mixed>
     */
    private function task(EntityInterface $type): array
    {
        // the fixtures write the identity column with the values they carry, which leaves the
        // sequence where it started
        $this->advanceIdentity('Tasks', 'nid');

        return [
            'task_type_id' => $type->get('id'),
            // a task starts open; asked for by name because there is more than one state and an
            // unordered `first()` over them would pick whichever the database felt like
            'task_state_id' => $this->stateId(completed: false),
            'subject' => 'Written by the test',
            'priority' => 1,
        ];
    }

    /**
     * The one state that is, or is not, a closed one.
     *
     * @param bool $completed Whether the state wanted is one that closes a task.
     * @return string
     */
    private function stateId(bool $completed): string
    {
        return (string)$this->getTableLocator()->get('TaskStates')
            ->find()
            ->where(['completed' => $completed])
            ->firstOrFail()
            ->get('id');
    }
}
