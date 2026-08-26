<?php
declare(strict_types=1);

namespace App\Test\TestCase\CRM;

use App\CRM\Tasks;
use App\Test\Traits\ConfigureTestTrait;
use Cake\Http\TestSuite\HttpClientTrait;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\TestCase;
use Override;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\CRM\Tasks Test Case
 *
 * What the other application answers with is not what a template draws, and the difference is
 * what this covers: rows become task entities of this application, and the place of the network
 * each one names is looked up here rather than taken from the answer.
 */
#[UsesClass(Tasks::class)]
class TasksTest extends TestCase
{
    use ConfigureTestTrait;
    use HttpClientTrait;
    use LocatorAwareTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.AppUsers',
        'app.AccessPointTypes',
        'app.AccessPoints',
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

        $this->withConfigure([
            'Crm.url' => 'https://crm.example.com',
            'Crm.key' => 'secret',
            'Crm.tasks' => true,
        ]);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    #[Override]
    protected function tearDown(): void
    {
        $this->restoreConfigure();

        parent::tearDown();
    }

    /**
     * Both halves of the question have to agree, or the installation is left with no task manager
     * at all - which is the one answer that would be nobody's intention.
     *
     * @return void
     * @link \App\CRM\Tasks::areUsed()
     */
    public function testTheTasksAreTheOtherApplicationsOnlyWhenThereIsOneToAsk(): void
    {
        $this->assertTrue(Tasks::areUsed());

        $this->withConfigure(['Crm.url' => '']);
        $this->assertFalse(Tasks::areUsed(), 'A wish to use them is not an address to ask at.');

        $this->withConfigure(['Crm.url' => 'https://crm.example.com', 'Crm.tasks' => false]);
        $this->assertFalse(Tasks::areUsed(), 'An address to ask at is not a wish to use them.');
    }

    /**
     * A row becomes a task of this application, which is what gives a template the colour of the
     * state and the name of the priority without either being written out again.
     *
     * @return void
     * @link \App\CRM\Tasks::atAccessPoint()
     */
    public function testARowBecomesATaskThisApplicationCanDraw(): void
    {
        $place = $this->getTableLocator()->get('AccessPoints')->find()->firstOrFail();

        $this->answerWith(['access_point_id' => (string)$place->get('id')], [[
            'id' => 'a26f0ae4-3d9c-4a4f-9a2e-0f1b2c3d4e5f',
            'nid' => 42,
            'subject' => 'Swap the antenna',
            'priority' => 50,
            'access_point_id' => $place->get('id'),
            'task_state' => ['name' => 'New', 'color' => '#ffcccc'],
            'task_type' => ['name' => 'Transmitter service'],
            'user' => ['first_name' => 'Jane', 'last_name' => 'Doe'],
        ]], 1);

        $page = (new Tasks())->atAccessPoint((string)$place->get('id'))->orFail();

        $this->assertSame(1, $page->total);
        $task = $page->tasks[0];

        $this->assertSame('42', $task->get('number'));
        $this->assertSame('Swap the antenna', $task->get('subject'));
        // read off the state that came with it, so the row is coloured as it is over there
        $this->assertStringContainsString('background-color:', $task->get('style'));
        $this->assertSame(__d('tasks', 'Urgent'), $task->getPriorityName());
        $this->assertSame('Jane Doe', $task->get('user')->get('name'));
    }

    /**
     * Whoever else is on a task comes over as a list, not as one more record.
     *
     * A task names one person it is filed under and any number of people out on it, so the two
     * are read differently - and read wrong, the second would come out as an entity holding an
     * entity, which no template would draw.
     *
     * @return void
     * @link \App\CRM\Tasks::atAccessPoint()
     */
    public function testWhoElseIsOnATaskComesOverAsAList(): void
    {
        $place = $this->getTableLocator()->get('AccessPoints')->find()->firstOrFail();

        $this->answerWith(['access_point_id' => (string)$place->get('id')], [[
            'id' => 'a26f0ae4-3d9c-4a4f-9a2e-0f1b2c3d4e5f',
            'nid' => 42,
            'subject' => 'Two of us are going',
            'priority' => 0,
            'access_point_id' => $place->get('id'),
            'task_state' => ['name' => 'New', 'color' => '#ffcccc'],
            'task_type' => ['name' => 'Transmitter service'],
            'user' => ['first_name' => 'Jane', 'last_name' => 'Doe'],
            'collaborators' => [
                ['first_name' => 'John', 'last_name' => 'Roe'],
                ['first_name' => 'Rich', 'last_name' => 'Poe'],
            ],
        ]], 1);

        $task = (new Tasks())->atAccessPoint((string)$place->get('id'))->orFail()->tasks[0];

        $this->assertSame('John Roe, Rich Poe', $task->get('collaborator_names'));
    }

    /**
     * The other application holds the identifier of a place and nothing else about it, so the
     * place itself is looked up here - which is also what makes the summary line read in this
     * application's terms.
     *
     * @return void
     * @link \App\CRM\Tasks::atAccessPoint()
     */
    public function testThePlaceOfTheNetworkIsLookedUpHere(): void
    {
        $place = $this->getTableLocator()->get('AccessPoints')->find()->firstOrFail();

        $this->answerWith(['access_point_id' => (string)$place->get('id')], [[
            'id' => 'a26f0ae4-3d9c-4a4f-9a2e-0f1b2c3d4e5f',
            'nid' => 42,
            'subject' => 'Swap the antenna',
            'priority' => 0,
            'access_point_id' => $place->get('id'),
        ]], 1);

        $task = (new Tasks())->atAccessPoint((string)$place->get('id'))->orFail()->tasks[0];

        $this->assertNotNull($task->get('access_point'));
        $this->assertSame($place->get('name'), $task->get('access_point')->get('name'));
        // and the line under the subject says where it is, which is what it is for
        $this->assertStringContainsString((string)$place->get('name'), $task->getSummaryText(false));
    }

    /**
     * The count is what there was before any limit, so a card can say how many it is drawing out
     * of - a page that only counted its own rows would make a full card look like an empty one.
     *
     * @return void
     * @link \App\CRM\Tasks::unassigned()
     */
    public function testTheCountIsWhatThereWasBeforeTheLimit(): void
    {
        $this->answerWith(
            ['unassigned' => '1', 'active' => '1', 'limit' => '2'],
            [
                ['id' => 'a26f0ae4-3d9c-4a4f-9a2e-0f1b2c3d4e5f', 'nid' => 1, 'priority' => 0],
                ['id' => 'b37f0ae4-3d9c-4a4f-9a2e-0f1b2c3d4e5f', 'nid' => 2, 'priority' => 0],
            ],
            17,
        );

        $page = (new Tasks())->unassigned(2)->orFail();

        $this->assertCount(2, $page->tasks);
        $this->assertSame(17, $page->total);
    }

    /**
     * An installation with no customer relationship management asked nothing, which is not the
     * same as having been answered with nothing.
     *
     * @return void
     * @link \App\CRM\Tasks::atAccessPoint()
     */
    public function testWithNothingToAskNothingIsAsked(): void
    {
        $this->withConfigure(['Crm.url' => '', 'Crm.key' => '']);

        $answer = (new Tasks())->atAccessPoint('9c4f6b1e-2f0a-4d3c-9a71-6b0d5f8e2a14');

        $this->assertFalse($answer->ok());
        $this->assertFalse($answer->unanswered(), 'Nobody asked, so nothing went unanswered.');
    }

    /**
     * Something answered, but not this - which is a failure rather than an empty list, or a card
     * would quietly say there is no work to do.
     *
     * @return void
     * @link \App\CRM\Tasks::atAccessPoint()
     */
    public function testAnAnswerToADifferentQuestionIsNotReadAsEmpty(): void
    {
        $place = '9c4f6b1e-2f0a-4d3c-9a71-6b0d5f8e2a14';

        $this->mockClientGet(
            $this->urlFor(['access_point_id' => $place]),
            $this->newClientResponse(200, ['Content-Type: application/json'], '{"something":"else"}'),
        );

        $answer = (new Tasks())->atAccessPoint($place);

        $this->assertFalse($answer->ok());
        $this->assertTrue($answer->unanswered());
    }

    /**
     * Let the other application answer one search.
     *
     * @param array<string, string> $query The cut being asked for.
     * @param array<mixed> $tasks The rows to answer with.
     * @param int $total How many there are said to be in all.
     * @return void
     */
    private function answerWith(array $query, array $tasks, int $total): void
    {
        $this->mockClientGet(
            $this->urlFor($query),
            $this->newClientResponse(
                200,
                ['Content-Type: application/json'],
                (string)json_encode(['tasks' => $tasks, 'total' => $total]),
            ),
        );
    }

    /**
     * Where a search is asked for, spelled the way the client asks for it.
     *
     * @param array<string, string> $query The cut being asked for.
     * @return string
     */
    private function urlFor(array $query): string
    {
        return 'https://crm.example.com/api/tasks/search.json?'
            . http_build_query($query + ['api_key' => 'secret'], '', '&', PHP_QUERY_RFC3986);
    }
}
