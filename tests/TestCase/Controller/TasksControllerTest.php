<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\TasksController;
use App\Model\Entity\Task;
use App\Test\Traits\ControllerTestTrait;
use Cake\I18n\Date;
use Cake\I18n\DateTime;
use Cake\TestSuite\EmailTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Controller\TasksController Test Case
 *
 * Smoke tests: every action is requested once and has to answer. They are deliberately shallow -
 * their job is to notice an action that stopped answering at all, which is where the query building
 * bugs turn up.
 */
#[UsesClass(TasksController::class)]
class TasksControllerTest extends TestCase
{
    use ControllerTestTrait;
    use EmailTrait;
    use IntegrationTestTrait;

    /**
     * Access point the nested routes hang off.
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
     * The listing renders.
     *
     * @return void
     * @link \App\Controller\TasksController::index()
     */
    public function testIndex(): void
    {
        $this->login();
        $this->get('/tasks');

        $this->assertResponseOk();
    }

    /**
     * The listing renders with the search filled in, which builds a different query than the plain
     * listing does and is therefore worth requesting on its own.
     *
     * @return void
     * @link \App\Controller\TasksController::index()
     */
    public function testIndexWithSearch(): void
    {
        $this->login();
        $this->get('/tasks?search=Lorem');

        $this->assertResponseOk();
    }

    /**
     * The detail of a record renders.
     *
     * @return void
     * @link \App\Controller\TasksController::view()
     */
    public function testView(): void
    {
        $this->login();
        $this->get('/tasks/view/' . $this->firstId('Tasks'));

        $this->assertResponseOk();
    }

    /**
     * The form for a new record renders.
     *
     * @return void
     * @link \App\Controller\TasksController::add()
     */
    public function testAdd(): void
    {
        $this->login();
        $this->get('/tasks/add');

        $this->assertResponseOk();
    }

    /**
     * The form of an existing record renders.
     *
     * @return void
     * @link \App\Controller\TasksController::edit()
     */
    public function testEdit(): void
    {
        $this->login();
        $this->get('/tasks/edit/' . $this->firstId('Tasks'));

        $this->assertResponseOk();
    }

    /**
     * The delete action runs and redirects. Whether the record really goes depends on what else
     * still references it, which is the application rules' business rather than this test's.
     *
     * @return void
     * @link \App\Controller\TasksController::delete()
     */
    public function testDelete(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/tasks/delete/' . $this->firstId('Tasks'));

        $this->assertRedirect();
    }

    /**
     * Added under its access point, the record is filed under it without the form saying so.
     *
     * The form under an access point leaves that field out - the route already says which one it
     * is, and the controller fills it in. Posting it in the body instead, as a test reaching the
     * flat route does, asks a different question and leaves this one unasked.
     *
     * @return void
     * @link \App\Controller\TasksController::add()
     */
    public function testAddUnderTheRouteFilesItUnderTheRoute(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        // the fixtures write the identity column with the values they carry, which leaves the
        // sequence where it started
        $this->advanceIdentity('Tasks', 'nid');

        $before = $this->idsIn('Tasks');
        $this->post('/access-points/' . self::ACCESS_POINT_ID . '/tasks/add', [
            'task_state_id' => $this->firstId('TaskStates'),
            'task_type_id' => $this->firstId('TaskTypes'),
            'subject' => 'Nested task',
        ]);

        $this->assertRedirect();
        $added = $this->addedRecord('Tasks', $before);
        $this->assertSame(self::ACCESS_POINT_ID, $added->get('access_point_id'));
    }

    /**
     * A change made on the form reaches the record.
     *
     * The assignment is cleared along with it, so that the save is the only thing this asks about -
     * a task left assigned to somebody else notifies them, which the tests below are for.
     *
     * @return void
     * @link \App\Controller\TasksController::edit()
     */
    public function testEditStoresTheChange(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $taskId = $this->firstId('Tasks');
        $this->post('/tasks/edit/' . $taskId, [
            'subject' => 'Renamed task',
            'user_id' => '',
        ]);

        $this->assertRedirect();
        $this->assertNoMailSent();
        $this->assertSame(
            'Renamed task',
            $this->getTableLocator()->get('Tasks')->get($taskId)->subject,
        );
    }

    /**
     * A task handed to somebody else tells them so.
     *
     * The person acting is logged in without an id of their own, so the assignment counts as being
     * to somebody else - which is what the notification hangs on.
     *
     * @return void
     * @link \App\Controller\TasksController::add()
     */
    public function testAddNotifiesTheUserTheTaskIsAssignedTo(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        // the fixtures write the identity column with the values they carry, which leaves the
        // sequence where it started
        $this->advanceIdentity('Tasks', 'nid');

        $this->post('/tasks/add', [
            'task_state_id' => $this->firstId('TaskStates'),
            'task_type_id' => $this->firstId('TaskTypes'),
            'subject' => 'Align the sector antenna',
            'user_id' => $this->firstId('AppUsers'),
        ]);

        $this->assertRedirect();
        $this->assertMailCount(1);
        $this->assertMailSentTo('operator@example.com');
        $this->assertMailSubjectContains('You have a new task');
        $this->assertMailContainsHtml('Align the sector antenna');
    }

    /**
     * A change to somebody else's task tells them about it, and says it is a change rather than a
     * task they have not seen before.
     *
     * @return void
     * @link \App\Controller\TasksController::edit()
     */
    public function testEditNotifiesTheUserTheTaskIsAssignedTo(): void
    {
        $this->login();
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $taskId = $this->firstId('Tasks');
        $this->post('/tasks/edit/' . $taskId, ['subject' => 'Realign the sector antenna']);

        $this->assertRedirect();
        $this->assertMailCount(1);
        $this->assertMailSentTo('operator@example.com');
        $this->assertMailSubjectContains('You have changes in task');
        $this->assertMailContainsHtml('Realign the sector antenna');
    }

    /**
     * The listing can be narrowed to what wants attention: a deadline near or past, or an
     * urgent mark whatever the date says.
     *
     * A deadline counted from today cannot be put in a fixture, so the tasks this asks
     * about are written here.
     *
     * @return void
     * @link \App\Controller\TasksController::index()
     */
    public function testIndexNarrowedToPressingTasks(): void
    {
        $pressing = $this->openTask(['critical_date' => Date::today()->addDays(2)]);
        $urgent = $this->openTask(['priority' => Task::PRIORITY_URGENT]);
        $quiet = $this->openTask(['critical_date' => Date::today()->addDays(400)]);

        $this->login();
        $this->get('/tasks?pressing=1&stale=0&show_completed=0&user_id=');

        $this->assertResponseOk();

        $ids = $this->listedTaskIds();
        $this->assertContains($pressing->id, $ids);
        $this->assertContains($urgent->id, $ids, 'urgent counts whatever its date says');
        $this->assertNotContains($quiet->id, $ids);
    }

    /**
     * The listing can be narrowed to what has lain untouched. Nothing brings a forgotten
     * task back on its own, so this is what stands in for that.
     *
     * @return void
     * @link \App\Controller\TasksController::index()
     */
    public function testIndexNarrowedToStaleTasks(): void
    {
        $stale = $this->openTask([]);
        $fresh = $this->openTask([]);

        // the timestamp behavior writes `modified` on save, so it is set aside afterwards
        $this->getTableLocator()->get('Tasks')->updateAll(
            ['modified' => DateTime::now()->subDays(90)],
            ['id' => $stale->id],
        );

        $this->login();
        $this->get('/tasks?pressing=0&stale=1&show_completed=0&user_id=');

        $this->assertResponseOk();

        $ids = $this->listedTaskIds();
        $this->assertContains($stale->id, $ids);
        $this->assertNotContains($fresh->id, $ids);
    }

    /**
     * An unfinished task, in a state that counts as unfinished.
     *
     * @param array<string, mixed> $data What this task differs by.
     * @return \App\Model\Entity\Task
     */
    private function openTask(array $data): Task
    {
        // the fixtures write the identity column with the values they carry, which leaves the
        // sequence where it started
        $this->advanceIdentity('Tasks', 'nid');

        $states = $this->getTableLocator()->get('TaskStates');
        $open = $states->find()->where(['completed' => false])->firstOrFail();

        $tasks = $this->getTableLocator()->get('Tasks');

        /** @var \App\Model\Entity\Task $task */
        $task = $tasks->saveOrFail($tasks->newEntity($data + [
            'task_type_id' => $this->firstId('TaskTypes'),
            'task_state_id' => $open->get('id'),
            'subject' => 'Written by the test',
            'priority' => Task::PRIORITY_NORMAL,
        ]));

        return $task;
    }

    /**
     * The ids of the tasks the listing came back with.
     *
     * @return list<string>
     */
    private function listedTaskIds(): array
    {
        $ids = [];
        /** @var iterable<\App\Model\Entity\Task> $tasks */
        $tasks = $this->viewVariable('tasks');
        foreach ($tasks as $task) {
            $ids[] = $task->id;
        }

        return $ids;
    }
}
