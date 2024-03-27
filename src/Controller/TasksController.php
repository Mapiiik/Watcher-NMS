<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Form\Form;
use Cake\I18n\Date;
use Cake\I18n\DateTime;
use Cake\Mailer\Mailer;
use Cake\ORM\Query\SelectQuery;
use Cake\Validation\Validation;
use Cake\View\Helper\HtmlHelper;
use Cake\View\View;
use Exception;

/**
 * Tasks Controller
 *
 * @property \App\Model\Table\TasksTable $Tasks
 * @method \App\Model\Entity\Task[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class TasksController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        // persistent filter data
        if (!is_null($this->getRequest()->getQuery('show_completed'))) {
            $this->getRequest()->getSession()->write(
                'Config.Tasks.filter.show_completed',
                $this->getRequest()->getQuery('show_completed')
            );
        }
        if (!is_null($this->getRequest()->getQuery('user_id'))) {
            $this->getRequest()->getSession()->write(
                'Config.Tasks.filter.user_id',
                $this->getRequest()->getQuery('user_id')
            );
        }
        if (!is_null($this->getRequest()->getQuery('task_type_id'))) {
            $this->getRequest()->getSession()->write(
                'Config.Tasks.filter.task_type_id',
                $this->getRequest()->getQuery('task_type_id')
            );
        }
        if (!is_null($this->getRequest()->getQuery('task_state_id'))) {
            $this->getRequest()->getSession()->write(
                'Config.Tasks.filter.task_state_id',
                $this->getRequest()->getQuery('task_state_id')
            );
        }
        if (!is_null($this->getRequest()->getQuery('access_point_id'))) {
            $this->getRequest()->getSession()->write(
                'Config.Tasks.filter.access_point_id',
                $this->getRequest()->getQuery('access_point_id')
            );
        }
        if (!is_null($this->getRequest()->getQuery('search'))) {
            $this->getRequest()->getSession()->write(
                'Config.Tasks.filter.search',
                $this->getRequest()->getQuery('search')
            );
        }
        $filter = $this->getRequest()->getSession()->read('Config.Tasks.filter');

        // filter
        $conditions = [];
        if (isset($this->access_point_id)) {
            $conditions[] = [
                'Tasks.access_point_id' => $this->access_point_id,
            ];
        }

        $show_completed = $filter['show_completed'] ?? null;
        if (empty($show_completed)) {
            $conditions[] = [
                'TaskStates.completed' => 0,
            ];
        }
        $user_id = $filter['user_id'] ?? $this->getRequest()->getAttribute('identity')['id'] ?? null;
        if (!empty($user_id)) {
            if ($user_id === 'none') {
                $conditions[] = [
                    'Users.id IS' => null,
                ];
            } elseif (Validation::uuid($user_id)) {
                $conditions[] = [
                    'Users.id' => $user_id,
                ];
            }
        }
        $task_type_id = $filter['task_type_id'] ?? null;
        if (Validation::uuid($task_type_id)) {
            $conditions[] = [
                'Tasks.task_type_id' => $task_type_id,
            ];
        }
        $task_state_id = $filter['task_state_id'] ?? null;
        if (Validation::uuid($task_state_id)) {
            $conditions[] = [
                'Tasks.task_state_id' => $task_state_id,
            ];
        }
        $access_point_id = $filter['access_point_id'] ?? null;
        if (Validation::uuid($access_point_id)) {
            $conditions[] = [
                'Tasks.access_point_id' => $access_point_id,
            ];
        }
        $search = $filter['search'] ?? null;
        if (!empty($search)) {
            $conditions[] = [
                'OR' => [
                    'Tasks.subject ILIKE' => '%' . trim($search) . '%',
                    'Tasks.text ILIKE' => '%' . trim($search) . '%',
                ] + (
                    is_numeric($search) ?
                    [
                        'Tasks.nid' => (int)trim($search),
                    ] : []
                ),
            ];
        }

        // filter form
        $filterForm = new Form();
        $filterForm->setData([
            'show_completed' => $show_completed,
            'user_id' => $user_id,
            'task_type_id' => $task_type_id,
            'task_state_id' => $task_state_id,
            'access_point_id' => $access_point_id,
            'search' => $search,
        ]);
        $this->set('filterForm', $filterForm);

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

        $tasks = $this->paginate($this->Tasks->find(
            'all',
            contain: [
                'AccessPoints',
                'Users',
                'TaskStates',
                'TaskTypes',
            ],
            conditions: $conditions
        ));
        $users = $this->Tasks->Users
            ->find()
            ->orderBy([
                'active' => 'DESC',
                'last_name',
                'first_name',
            ])
            ->all()
            ->map(function ($user) {
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
                    ]]
                ),
                [
                    'escape' => false,
                ]
            );
        }

        $taskTypes = $this->Tasks->TaskTypes->find('list', order: [
            'name',
        ]);
        $taskStates = $this->Tasks->TaskStates->find('list', order: [
            'name',
        ]);
        $accessPoints = $this->Tasks->AccessPoints->find('list', order: [
            'name',
        ]);

        $this->set(compact('tasks', 'taskTypes', 'taskStates', 'users', 'accessPoints'));
    }

    /**
     * View method
     *
     * @param string|null $id Task id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
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
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $task = $this->Tasks->newEmptyEntity();

        if (isset($this->access_point_id)) {
            $task->access_point_id = $this->access_point_id;
        }

        if ($this->getRequest()->is('post')) {
            $task = $this->Tasks->patchEntity($task, $this->getRequest()->getData());

            if ($this->getRequest()->getData('refresh') == 'refresh') {
                // only refresh
            } else {
                if ($this->Tasks->save($task)) {
                    // send email notification
                    if (
                        $task->__isset('user_id')
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
        $taskTypes = $this->Tasks->TaskTypes->find('list', order: [
            'name',
        ]);
        $taskStates = $this->Tasks->TaskStates->find('list', order: [
            'name',
        ]);
        $accessPoints = $this->Tasks->AccessPoints->find('list', order: [
            'name',
        ]);
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
            ->map(function ($user) {
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
    }

    /**
     * Edit method
     *
     * @param string|null $id Task id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
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
                        $task->__isset('user_id')
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
        $taskStates = $this->Tasks->TaskStates->find('list', order: [
            'name',
        ]);
        $taskTypes = $this->Tasks->TaskTypes->find('list', order: [
            'name',
        ]);
        $accessPoints = $this->Tasks->AccessPoints->find('list', order: [
            'name',
        ]);
        $users = $this->Tasks->Users
            ->find()
            ->orderBy([
                'active' => 'DESC',
                'last_name',
                'first_name',
            ])
            ->all()
            ->map(function ($user) {
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
    }

    /**
     * Delete method
     *
     * @param string|null $id Task id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null)
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
    private function taskTextHeader()
    {
        $text = '';

        $identity = $this->getRequest()->getAttribute('identity');
        $text .= '------------------------------------------------------------' . PHP_EOL;
        $text .= ' ' . ($identity['first_name'] ?? '') . ' ' . ($identity['last_name'] ?? '');
        $text .= ' (' . DateTime::now() . ')' . PHP_EOL;
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

        $mailer = new Mailer('default');

        $mailer->setTo($task->user->email, $task->user->name);

        if ($new) {
            $title = __('You have a new task # {0}', $task->number);
        } else {
            $title = __('You have changes in task # {0}', $task->number);
        }

        $mailer->setSubject($title . ' - ' . $task->summary_text);
        $mailer->setEmailFormat('html');

        $mailer->viewBuilder()
            ->setLayout('default')
            ->setTemplate('task-notification');

        $mailer->setViewVars(['title' => $title, 'task' => $task]);

        try {
            $mailer->deliver();
            $this->Flash->success(__('Notification email sent.') . ' (' . $task->user->email . ')');

            return true;
        } catch (Exception $e) {
            $this->Flash->error(__('The notification email could not be sent.') . ' (' . $e->getMessage() . ')');

            return false;
        }
    }
}
