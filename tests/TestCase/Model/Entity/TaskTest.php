<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\AccessPoint;
use App\Model\Entity\Task;
use App\Model\Entity\TaskType;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Model\Entity\Task Test Case
 */
#[UsesClass(Task::class)]
class TaskTest extends TestCase
{
    /**
     * The summary is what a task is recognised by in lists and notifications, so it carries the
     * subject, the access point it belongs to and the phone to call.
     *
     * @return void
     * @link \App\Model\Entity\Task::_getSummaryText()
     */
    public function testTheSummaryCarriesTheSubjectTheAccessPointAndThePhone(): void
    {
        $task = new Task([
            'subject' => 'Antenna realignment',
            'phone' => '+420 111 222 333',
            'access_point' => new AccessPoint([
                'name' => 'Jablonec - water tower',
            ]),
        ]);

        $this->assertSame(
            'Antenna realignment - Jablonec - water tower, +420 111 222 333',
            $task->summary_text,
        );
    }

    /**
     * A task filed without a subject is recognised by its type instead of showing up blank.
     *
     * @return void
     * @link \App\Model\Entity\Task::_getSummaryText()
     */
    public function testATaskWithoutASubjectIsShownByItsType(): void
    {
        $task = new Task([
            'task_type' => new TaskType([
                'name' => 'Installation',
            ]),
        ]);

        $this->assertSame('Installation', $task->summary_text);
    }

    /**
     * A task tied to no access point and no phone leaves no separator hanging behind the subject.
     *
     * @return void
     * @link \App\Model\Entity\Task::_getSummaryText()
     */
    public function testASubjectOnItsOwnHasNoTrailingSeparator(): void
    {
        $task = new Task([
            'subject' => 'Antenna realignment',
        ]);

        $this->assertSame('Antenna realignment', $task->summary_text);
    }

    /**
     * A task with nothing to go on at all is empty rather than a bare separator.
     *
     * @return void
     * @link \App\Model\Entity\Task::_getSummaryText()
     */
    public function testATaskWithNothingToGoOnIsEmpty(): void
    {
        $task = new Task();

        $this->assertSame('', $task->summary_text);
    }
}
