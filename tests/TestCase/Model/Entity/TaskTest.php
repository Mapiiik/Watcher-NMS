<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\AccessPoint;
use App\Model\Entity\Task;
use App\Model\Entity\TaskType;
use App\Test\Traits\ConfigureTestTrait;
use Cake\TestSuite\TestCase;
use Override;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Model\Entity\Task Test Case
 */
#[UsesClass(Task::class)]
class TaskTest extends TestCase
{
    use ConfigureTestTrait;

    /**
     * setUp method
     *
     * @return void
     */
    #[Override]
    public function setUp(): void
    {
        parent::setUp();

        // a deployment names a region, a development machine names one in config/.env and CI names
        // none - the tests that read numbers against one say so themselves
        $this->withConfigure([
            'Phones.stripPrefixForSummary' => false,
            'Phones.defaultRegion' => 'CZ',
        ]);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    #[Override]
    public function tearDown(): void
    {
        $this->restoreConfigure();

        parent::tearDown();
    }

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

    /**
     * A number from the configured region is dialled without its prefix at home, so a deployment
     * that asks for it gets the short form - however the number happens to be stored.
     *
     * @return void
     * @link \App\Model\Entity\Task::_getSummaryText()
     */
    public function testAPhoneFromTheRegionIsShortenedWhenConfigured(): void
    {
        $this->withConfigure(['Phones.stripPrefixForSummary' => true]);

        $task = new Task([
            'subject' => 'Antenna realignment',
            'phone' => '+420601234567',
        ]);

        $this->assertSame('Antenna realignment, 601234567', $task->summary_text);
    }

    /**
     * A number from anywhere else keeps the prefix it cannot be dialled without.
     *
     * @return void
     * @link \App\Model\Entity\Task::_getSummaryText()
     */
    public function testAForeignPhoneKeepsItsPrefix(): void
    {
        $this->withConfigure(['Phones.stripPrefixForSummary' => true]);

        $task = new Task([
            'subject' => 'Antenna realignment',
            'phone' => '+1 650-253-0000',
        ]);

        $this->assertSame('Antenna realignment, +1 650-253-0000', $task->summary_text);
    }

    /**
     * A deployment that has not asked for the short form gets the number as it stands.
     *
     * @return void
     * @link \App\Model\Entity\Task::_getSummaryText()
     */
    public function testAPhoneIsLeftAloneWhenNotConfiguredToBeShortened(): void
    {
        $task = new Task([
            'subject' => 'Antenna realignment',
            'phone' => '+420 601 234 567',
        ]);

        $this->assertSame('Antenna realignment, +420 601 234 567', $task->summary_text);
    }
}
