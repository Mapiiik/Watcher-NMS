<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Phones\Formatter as PhoneFormatter;
use Cake\Core\Configure;
use Override;
use Tasks\Model\Entity\Task as TasksTask;

/**
 * Task Entity
 *
 * On top of the shared task: what this application files a task under, and the line that reads
 * it out.
 *
 * @property string|null $access_point_id
 *
 * @property \App\Model\Entity\AccessPoint|null $access_point
 */
class Task extends TasksTask
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'created' => true,
        'created_by' => true,
        'modified' => true,
        'modified_by' => true,
        'task_state_id' => true,
        'task_type_id' => true,
        'subject' => true,
        'text' => true,
        'priority' => true,
        'user_id' => true,
        'email' => true,
        'phone' => true,
        'start_date' => true,
        'finish_date' => true,
        'estimated_date' => true,
        'critical_date' => true,
        'access_point_id' => true,
        'task_state' => true,
        'task_type' => true,
        'user' => true,
        'access_point' => true,
    ];

    /**
     * The one line that says what a task is about: where it is and how to reach whoever is
     * there.
     *
     * Whoever already shows the subject - a listing that has it as its heading, say - asks
     * for it to be left out rather than reading it twice.
     *
     * @param bool $with_subject Whether the subject heads the line.
     * @return string
     */
    #[Override]
    public function getSummaryText(bool $with_subject = true): string
    {
        $phoneNumber = $this->phone;
        if (isset($phoneNumber) && Configure::read('Phones.stripPrefixForSummary') === true) {
            $phoneNumber = PhoneFormatter::toLocal($phoneNumber);
        }

        // The subject and the access point head the line, the phone follows
        // behind a comma.
        return implode(', ', array_filter([
            implode(' - ', array_filter([
                $with_subject ? $this->subject ?? $this->task_type->name ?? null : null,
                $this->access_point->name ?? null,
            ])),
            $phoneNumber,
        ]));
    }
}
