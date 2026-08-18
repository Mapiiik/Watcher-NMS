<?php
declare(strict_types=1);

namespace App\Controller;

use App\Maps\TaskMap;
use Cake\Form\Form;
use Cake\Http\Response;
use Cake\I18n\Date;
use Cake\I18n\DateTime;
use Cake\Mailer\Mailer;
use Cake\ORM\Query\SelectQuery;
use Cake\Utility\Hash;
use Cake\Validation\Validation;
use Cake\View\Helper\HtmlHelper;
use Cake\View\View;
use Exception;
use Settings\Utility\Settings;

/**
 * Tasks Controller
 *
 * @property \App\Model\Table\TasksTable $Tasks
 */
class TasksController extends AppController
{
    /**
     * Map method
     *
     * The open tasks drawn where they are to be done, which is what planning a round asks for.
     *
     * @return void Renders view
     */
    public function map(): void
    {
        $map = (new TaskMap(new HtmlHelper(new View())))->draw();

        $this->set('mapMarkers', $map->markers);
        $this->set('mapPolylines', $map->polylines);
    }

    /**
     * Index method
     *
     * @return void Renders view
     */
    public function index(): void
    {
        // persistent options
        if (!is_null($this->getRequest()->getQuery('expandable_text'))) {
            $this->getRequest()->getSession()->write(
                'Config.Tasks.options.expandable_text',
                $this->getRequest()->getQuery('expandable_text'),
            );
        }

        $options = $this->getRequest()->getSession()->read('Config.Tasks.options') ?? [];

        // expandable text
        $expandable_text = toBool(
            $options['expandable_text']
                ?? Hash::get($this->user_settings, 'tasks.expandable_text', false),
        );
        $this->set('expandableText', $expandable_text);

        // persistent filter data
        if (!is_null($this->getRequest()->getQuery('show_completed'))) {
            $this->getRequest()->getSession()->write(
                'Config.Tasks.filter.show_completed',
                $this->getRequest()->getQuery('show_completed'),
            );
        }
        if (!is_null($this->getRequest()->getQuery('pressing'))) {
            $this->getRequest()->getSession()->write(
                'Config.Tasks.filter.pressing',
                $this->getRequest()->getQuery('pressing'),
            );
        }
        if (!is_null($this->getRequest()->getQuery('stale'))) {
            $this->getRequest()->getSession()->write(
                'Config.Tasks.filter.stale',
                $this->getRequest()->getQuery('stale'),
            );
        }
        if (!is_null($this->getRequest()->getQuery('user_id'))) {
            $this->getRequest()->getSession()->write(
                'Config.Tasks.filter.user_id',
                $this->getRequest()->getQuery('user_id'),
            );
        }
        if (!is_null($this->getRequest()->getQuery('task_type_id'))) {
            $this->getRequest()->getSession()->write(
                'Config.Tasks.filter.task_type_id',
                $this->getRequest()->getQuery('task_type_id'),
            );
        }
        if (!is_null($this->getRequest()->getQuery('task_state_id'))) {
            $this->getRequest()->getSession()->write(
                'Config.Tasks.filter.task_state_id',
                $this->getRequest()->getQuery('task_state_id'),
            );
        }
        if (!is_null($this->getRequest()->getQuery('access_point_id'))) {
            $this->getRequest()->getSession()->write(
                'Config.Tasks.filter.access_point_id',
                $this->getRequest()->getQuery('access_point_id'),
            );
        }
        if (!is_null($this->getRequest()->getQuery('search'))) {
            $this->getRequest()->getSession()->write(
                'Config.Tasks.filter.search',
                $this->getRequest()->getQuery('search'),
            );
        }
        $filter = $this->getRequest()->getSession()->read('Config.Tasks.filter') ?? [];

        // filter
        $conditions = [];
        if ($this->access_point_id !== null) {
            $conditions[] = [
                'Tasks.access_point_id' => $this->access_point_id,
            ];
        }

        // filter by completed
        // filter by what wants attention - the same reading the dashboard cards are drawn
        // from, so a card and the listing it points at hold the same tasks
        $pressing = toBool($filter['pressing'] ?? null) ?? false;
        $stale = toBool($filter['stale'] ?? null) ?? false;

        $show_completed = $filter['show_completed'] ?? null;
        if (empty($show_completed)) {
            $conditions[] = [
                'TaskStates.completed' => 0,
            ];
        }

        // filter by user
        if (Hash::get($this->user_settings, 'tasks.all_by_default', false)) {
            $user_id = $filter['user_id'] ?? null;
        } else {
            $user_id = $filter['user_id'] ?? $this->getRequest()->getAttribute('identity')['id'] ?? null;
        }
        if (!empty($user_id)) {
            if ($user_id === 'none') {
                $conditions[] = [
                    'Users.id IS' => null,
                ];
            } elseif (is_string($user_id) && Validation::uuid($user_id)) {
                $conditions[] = [
                    'Users.id' => $user_id,
                ];
            }
        }

        // filter by task type
        $task_type_id = $filter['task_type_id'] ?? null;
        if (is_string($task_type_id) && Validation::uuid($task_type_id)) {
            $conditions[] = [
                'Tasks.task_type_id' => $task_type_id,
            ];
        }

        // filter by task state
        $task_state_id = $filter['task_state_id'] ?? null;
        if (is_string($task_state_id) && Validation::uuid($task_state_id)) {
            $conditions[] = [
                'Tasks.task_state_id' => $task_state_id,
            ];
        }

        // filter by access point
        $access_point_id = $filter['access_point_id'] ?? null;
        if (is_string($access_point_id) && Validation::uuid($access_point_id)) {
            $conditions[] = [
                'Tasks.access_point_id' => $access_point_id,
            ];
        }

        // search
        $search = $filter['search'] ?? null;
        if (!empty($search)) {
            $search = trim((string)$search);
            $conditions[] = [
                'OR' => [
                    'Tasks.subject ILIKE' => '%' . $search . '%',
                    'Tasks.text ILIKE' => '%' . $search . '%',
                ] + (
                    ctype_digit($search) && strlen($search) <= 10 ? // strlen($search) <= 19 for BIGINT
                    [
                        'Tasks.nid' => (int)$search,
                    ] : []
                ),
            ];
        }

        // filter form
        $filterForm = new Form();
        $filterForm->setData([
            'expandable_text' => $expandable_text,
            'show_completed' => $show_completed,
            'pressing' => $pressing,
            'stale' => $stale,
            'user_id' => $user_id,
            'task_type_id' => $task_type_id,
            'task_state_id' => $task_state_id,
            'access_point_id' => $access_point_id,
            'search' => $search,
        ]);
        $this->set('filterForm', $filterForm);

        // pagination settings
        $this->paginate = [
            'sortableFields' => [
                'nid',
                'task_type_id',
                'priority',
                'TaskStates.priority',
                'user_id',
                'subject',
                'text',
                'access_point_id',
                'start_date',
                'estimated_date',
                'critical_date',
                'finish_date',
            ],
            'order' => [
                'TaskStates.priority' => 'DESC',
                'priority' => 'DESC',
                'nid' => 'DESC',
            ],
        ];

        // paginate results
        $query = $this->Tasks->find(
            'all',
            contain: [
                'AccessPoints',
                'Users',
                'TaskStates',
                'TaskTypes',
            ],
            conditions: $conditions,
        );

        if ($pressing) {
            $query->find('pressing', within_days: (int)Settings::get(
                'core.dashboard.tasks.critical_within_days',
                7,
            ));
        }
        if ($stale) {
            $query->find('stale', days: (int)Settings::get('core.dashboard.tasks.stale_after_days', 30));
        }

        $tasks = $this->paginate($query);

        $users = $this->Tasks->Users
            ->find()
            ->orderBy([
                'active' => 'DESC',
                'last_name',
                'first_name',
            ])
            ->all()
            ->map(function ($user): array {
                return [
                    'value' => $user->id,
                    'text' => $user->name_for_lists,
                    'style' => $user->active ? null : 'color: darkgray;',
                ];
            })
            ->prependItem([
                'value' => 'none',
                'text' => '(' . __('none') . ')',
                'style' => 'color: darkgray;',
            ])
            ->toList();

        // get the number of unassigned tasks
        $number_of_unassigned_tasks = $this->Tasks
            ->find()
            ->matching('TaskStates', function (SelectQuery $query) {
                return $query->where([
                    'TaskStates.completed' => false,
                ]);
            })
            ->notMatching('Users')
            ->count();

        // show warning if there are some unassigned tasks
        if ($number_of_unassigned_tasks > 0) {
            $this->Flash->warning(
                (new HtmlHelper(new View($this->getRequest())))->link(
                    __n(
                        'There was {0} unfinished task found that does not have a user assigned.',
                        'There were {0} unfinished tasks found that do not have a user assigned.',
                        $number_of_unassigned_tasks,
                        $number_of_unassigned_tasks,
                    ),
                    ['?' => [
                        'user_id' => 'none',
                        'task_type_id' => '',
                        'task_state_id' => '',
                        'access_point_id' => '',
                        'show_completed' => 0,
                    ]],
                ),
                [
                    'escape' => false,
                ],
            );
        }

        $taskTypes = $this->Tasks->TaskTypes->find('list', order: ['name'])->all();
        $taskStates = $this->Tasks->TaskStates->find('list', order: ['name'])->all();
        $accessPoints = $this->Tasks->AccessPoints
            ->find('list', order: ['name'])
            ->all();

        $this->set(compact('tasks', 'taskTypes', 'taskStates', 'users', 'accessPoints'));
    }

    /**
     * View method
     *
     * @param string|null $id Task id.
     * @return void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $task = $this->Tasks->get($id, contain: [
            'AccessPoints',
            'Creators',
            'Modifiers',
            'TaskStates',
            'TaskTypes',
            'Users',
        ]);

        $this->set(compact('task'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add(): ?Response
    {
        $task = $this->Tasks->newEmptyEntity();

        if ($this->access_point_id !== null) {
            $task->access_point_id = $this->access_point_id;
        }

        if ($this->getRequest()->is('post')) {
            $task = $this->Tasks->patchEntity(
                $task,
                $this->dataWithAdditionalParameters($this->Tasks, $this->getRequest()->getData()),
            );

            if ($this->getRequest()->getData('refresh') == 'refresh') {
                // only refresh
            } else {
                if ($this->Tasks->save($task)) {
                    // send email notification
                    if (
                        $task->user_id !== null
                        && $task->user_id != ($this->getRequest()->getAttribute('identity')['id'] ?? null)
                    ) {
                        $this->sendNotificationEmail($task->id, true);
                    }

                    $this->Flash->success(__('The task has been saved.'));

                    return $this->afterAddRedirect(['action' => 'view', $task->id]);
                }
                $this->Flash->error(__('The task could not be saved. Please, try again.'));
            }
        }
        $taskTypes = $this->Tasks->TaskTypes->find('list', order: ['name'])->all();
        $taskStates = $this->Tasks->TaskStates->find('list', order: ['name'])->all();
        $accessPoints = $this->Tasks->AccessPoints
            ->find('active')
            ->find('list', order: ['name'])
            ->all();

        $users = $this->Tasks->Users
            ->find()
            ->where([
                'active' => true, // only active users
            ])
            ->orderBy([
                'active' => 'DESC',
                'last_name',
                'first_name',
            ])
            ->all()
            ->map(function ($user): array {
                return [
                    'value' => $user->id,
                    'text' => $user->name_for_lists,
                    'style' => $user->active ? null : 'color: darkgray;',
                ];
            });

        // preset start date
        if (empty($task->start_date)) {
            $task->start_date = Date::now();
        }
        // preset user
        if (empty($task->user_id)) {
            $task->user_id = $this->getRequest()->getAttribute('identity')['id'] ?? null;
        }

        // add task text header
        $task->text .= $this->taskTextHeader();

        $this->set(compact('task', 'taskTypes', 'taskStates', 'users', 'accessPoints'));

        return null;
    }

    /**
     * Edit method
     *
     * @param string|null $id Task id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
    {
        $task = $this->Tasks->get($id, contain: []);
        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $task = $this->Tasks->patchEntity($task, $this->getRequest()->getData());

            if ($this->getRequest()->getData('refresh') == 'refresh') {
                // only refresh
            } else {
                if ($this->Tasks->save($task)) {
                    // send email notification
                    if (
                        $task->user_id !== null
                        && $task->user_id != ($this->getRequest()->getAttribute('identity')['id'] ?? null)
                    ) {
                        $this->sendNotificationEmail($task->id, false);
                    }

                    $this->Flash->success(__('The task has been saved.'));

                    return $this->afterEditRedirect(['action' => 'view', $task->id]);
                }
                $this->Flash->error(__('The task could not be saved. Please, try again.'));
            }
        }
        $taskTypes = $this->Tasks->TaskTypes->find('list', order: ['name'])->all();
        $taskStates = $this->Tasks->TaskStates->find('list', order: ['name'])->all();
        $accessPoints = $this->Tasks->AccessPoints
            ->find('list', order: ['name'])
            ->all();

        $users = $this->Tasks->Users
            ->find()
            ->orderBy([
                'active' => 'DESC',
                'last_name',
                'first_name',
            ])
            ->all()
            ->map(function ($user): array {
                return [
                    'value' => $user->id,
                    'text' => $user->name_for_lists,
                    'style' => $user->active ? null : 'color: darkgray;',
                ];
            });

        // add task text header
        if (!empty($task->text)) {
            $task->text .= PHP_EOL . PHP_EOL;
        }
        $task->text .= $this->taskTextHeader();

        $this->set(compact('task', 'taskTypes', 'taskStates', 'users', 'accessPoints'));

        return null;
    }

    /**
     * Delete method
     *
     * @param string|null $id Task id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->getRequest()->allowMethod(['post', 'delete']);
        $task = $this->Tasks->get($id);
        if ($this->Tasks->delete($task)) {
            $this->Flash->success(__('The task has been deleted.'));
        } else {
            $this->flashValidationErrors($task->getErrors());
            $this->Flash->error(__('The task could not be deleted. Please, try again.'));
        }

        return $this->afterDeleteRedirect(['action' => 'index']);
    }

    /**
     * Task text header method
     *
     * @return string Task text header.
     */
    private function taskTextHeader(): string
    {
        $text = '';

        $identity = $this->getRequest()->getAttribute('identity');
        $text .= '------------------------------------------------------------' . PHP_EOL;
        $text .= ' ' . ($identity['first_name'] ?? '') . ' ' . ($identity['last_name'] ?? '');
        $text .= ' (' . DateTime::now()->__toString() . ')' . PHP_EOL;
        $text .= '------------------------------------------------------------' . PHP_EOL;
        unset($identity);

        return $text;
    }

    /**
     * Send a task notification email
     *
     * @param string|null $id Task id.
     * @param bool $new This is new task.
     * @return bool Successfull?
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    private function sendNotificationEmail(?string $id = null, bool $new = false): bool
    {
        $task = $this->Tasks->get($id, contain: [
            'AccessPoints',
            'Creators',
            'Modifiers',
            'TaskStates',
            'TaskTypes',
            'Users',
        ]);

        if (!is_object($task->user) || empty($task->user->email)) {
            $this->Flash->error(__(
                'The notification email could not be sent because the user does not have an email address.',
            ));

            return false;
        }

        try {
            $mailer = new Mailer('default');

            $mailer->setTo($task->user->email, $task->user->name);

            $title = $new ?
                __('You have a new task # {0}', $task->number)
                : __('You have changes in task # {0}', $task->number);

            $mailer->setSubject($title . ' - ' . $task->summary_text);
            $mailer->setEmailFormat('html');

            $mailer->viewBuilder()
                ->setLayout('default')
                ->setTemplate('task-notification');

            $mailer->setViewVars(['title' => $title, 'task' => $task]);

            $mailer->deliver();
            $this->Flash->success(__('Notification email sent.') . ' (' . $task->user->email . ')');

            return true;
        } catch (Exception $e) {
            $this->Flash->error(__('The notification email could not be sent.') . ' (' . $e->getMessage() . ')');

            return false;
        }
    }
}
