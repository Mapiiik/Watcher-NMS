<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Colors\ColorThemeSelector;
use App\Phones\Formatter as PhoneFormatter;
use Cake\Core\Configure;

/**
 * Task Entity
 *
 * @property string $id
 * @property int $nid
 * @property string $task_state_id
 * @property string $task_type_id
 * @property string|null $subject
 * @property string|null $text
 * @property int $priority
 * @property string|null $user_id
 * @property string|null $email
 * @property string|null $phone
 * @property \Cake\I18n\Date|null $start_date
 * @property \Cake\I18n\Date|null $finish_date
 * @property \Cake\I18n\Date|null $estimated_date
 * @property \Cake\I18n\Date|null $critical_date
 * @property string|null $access_point_id
 * @property string $number
 * @property string $summary_text
 * @property string $style
 *
 * @property \App\Model\Entity\TaskState $task_state
 * @property \App\Model\Entity\TaskType $task_type
 * @property \App\Model\Entity\AppUser|null $user
 * @property \App\Model\Entity\AccessPoint|null $access_point
 */
class Task extends AppEntity
{
    /**
     * The priorities a task is offered, ordered from the least to the most pressing.
     */
    public const PRIORITY_LOW = -10;
    public const PRIORITY_NORMAL = 0;
    public const PRIORITY_HIGH = 10;
    public const PRIORITY_URGENT = 50;

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
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
     * getter for task number
     *
     * @return string
     */
    protected function _getNumber(): string
    {
        return strval($this->nid);
    }

    /**
     * getter for summary text
     *
     * @return string
     */
    protected function _getSummaryText(): string
    {
        return $this->getSummaryText();
    }

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

    /**
     * getter for style
     *
     * @return string
     */
    protected function _getStyle(): string
    {
        if (!isset($this->task_state->color)) {
            // no dynamic style
            return '';
        }

        $theme = Configure::read('UI.theme');
        $theme = is_string($theme) ? $theme : null;

        $backgroundColor = ColorThemeSelector::forTheme(
            $this->task_state->color,
            $theme,
        );

        return 'background-color: ' . $backgroundColor . ';';
    }

    /**
     * Get task priority options method
     *
     * @return array<int, string>
     */
    public function getPriorityOptions(): array
    {
        return [
            self::PRIORITY_LOW => __('Low'),
            self::PRIORITY_NORMAL => __('Normal'),
            self::PRIORITY_HIGH => __('High'),
            self::PRIORITY_URGENT => __('Urgent'),
        ];
    }

    /**
     * Get task priority name method
     *
     * @return string
     */
    public function getPriorityName(): string
    {
        return $this->getPriorityOptions()[$this->priority] ?? (string)$this->priority;
    }
}
