<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Task Entity
 *
 * @property \Cake\I18n\DateTime $created
 * @property string|null $created_by
 * @property \App\Model\Entity\AppUser|null $creator
 * @property \Cake\I18n\DateTime $modified
 * @property string|null $modified_by
 * @property \App\Model\Entity\AppUser|null $modifier
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
class Task extends Entity
{
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
        $number = strval($this->nid);

        return $number;
    }

    /**
     * getter for summary text
     *
     * @return string
     */
    protected function _getSummaryText(): string
    {
        $summary_text = $this->subject ?? $this->task_type->name ?? '';

        if (isset($this->access_point->name)) {
            $summary_text .= ' - ' . $this->access_point->name;
        }

        if (isset($this->phone)) {
            $summary_text .= ', ' . $this->phone;
        }

        return $summary_text;
    }

    /**
     * getter for style
     *
     * @return string
     */
    protected function _getStyle(): string
    {
        $style = '';

        if (isset($this->task_state->color)) {
            $style = 'background-color: ' . $this->task_state->color . ';';
        }

        return $style;
    }

    /**
     * Get task priority options method
     *
     * @return array<int, string>
     */
    public function getPriorityOptions(): array
    {
        return [
            -10 => __('Low'),
            0 => __('Normal'),
            10 => __('High'),
            50 => __('Urgent'),
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
