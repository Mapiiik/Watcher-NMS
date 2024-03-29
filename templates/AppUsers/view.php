<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AppUser $user
 * @var string $tableAlias
 */

$user = ${$tableAlias};
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __d('app_users', 'Actions') ?></h4>
            <?= $this->Html->link(
                __d('app_users', 'Edit User'),
                ['action' => 'edit', $user->id],
                ['class' => 'side-nav-item']
            ) ?>
            <?= $this->Form->postLink(
                __d('app_users', 'Delete User'),
                ['action' => 'delete', $user->id],
                [
                    'confirm' => __d('app_users', 'Are you sure you want to delete # {0}?', $user->id),
                    'class' => 'side-nav-item',
                ]
            ) ?>
            <?= $this->Html->link(
                __d('app_users', 'List Users'),
                ['action' => 'index'],
                ['class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(
                __d('app_users', 'New User'),
                ['action' => 'add'],
                ['class' => 'side-nav-item']
            ) ?>
        </div>
    </aside>
    <div class="column column-90">
        <div class="users view content">
            <h3><?= h($user->username) ?></h3>
            <div class="row">
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __d('app_users', 'Username') ?></th>
                            <td><?= h($user->username) ?></td>
                        </tr>
                        <tr>
                            <th><?= __d('app_users', 'Email') ?></th>
                            <td><?= h($user->email) ?></td>
                        </tr>
                        <tr>
                            <th><?= __d('app_users', 'First Name') ?></th>
                            <td><?= h($user->first_name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __d('app_users', 'Last Name') ?></th>
                            <td><?= h($user->last_name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __d('app_users', 'Role') ?></th>
                            <td><?= h($user->getRoleName()) ?></td>
                        </tr>
                        <tr>
                            <th><?= __d('app_users', 'Is Superuser') ?></th>
                            <td><?= $user->is_superuser ? __d('app_users', 'Yes') : __d('app_users', 'No'); ?></td>
                        </tr>
                        <tr>
                            <th><?= __d('app_users', 'Active') ?></th>
                            <td><?= $user->active ? __d('app_users', 'Yes') : __d('app_users', 'No'); ?></td>
                        </tr>
                    </table>
                </div>
                <div class="column">
                    <table>
                        <tr>
                            <th><?= __d('app_users', 'Api Token') ?></th>
                            <td><?= h($user->api_token) ?></td>
                        </tr>
                        <tr>
                            <th><?= __d('app_users', 'Token') ?></th>
                            <td><?= h($user->token) ?></td>
                        </tr>
                        <tr>
                            <th><?= __d('app_users', 'Token Expires') ?></th>
                            <td><?= h($user->token_expires) ?></td>
                        </tr>
                        <tr>
                            <th><?= __d('app_users', 'Activation Date') ?></th>
                            <td><?= h($user->activation_date) ?></td>
                        </tr>
                        <tr>
                            <th><?= __d('app_users', 'Tos Date') ?></th>
                            <td><?= h($user->tos_date) ?></td>
                        </tr>
                        <tr>
                            <th><?= __d('app_users', 'Secret') ?></th>
                            <td><?= h($user->secret) ?></td>
                        </tr>
                        <tr>
                            <th><?= __d('app_users', 'Secret Verified') ?></th>
                            <td><?= $user->secret_verified ? __d('app_users', 'Yes') : __d('app_users', 'No'); ?></td>
                        </tr>
                        <tr>
                            <th><?= __d('app_users', 'Additional Data') ?></th>
                            <td><?= h($user->additional_data) ?></td>
                        </tr>
                        <tr>
                            <th><?= __d('app_users', 'Last Login') ?></th>
                            <td><?= h($user->last_login) ?></td>
                        </tr>
                        <tr>
                            <th><?= __d('app_users', 'Created') ?></th>
                            <td><?= h($user->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __d('app_users', 'Modified') ?></th>
                            <td><?= h($user->modified) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="related">
                <h4><?= __d('app_users', 'Related Social Accounts') ?></h4>
                <?php if (!empty($user->social_accounts)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __d('app_users', 'Id') ?></th>
                            <th><?= __d('app_users', 'Provider') ?></th>
                            <th><?= __d('app_users', 'Username') ?></th>
                            <th><?= __d('app_users', 'Active') ?></th>
                            <th><?= __d('app_users', 'Reference') ?></th>
                            <th><?= __d('app_users', 'Avatar') ?></th>
                            <th><?= __d('app_users', 'Description') ?></th>
                            <th><?= __d('app_users', 'Link') ?></th>
                            <th><?= __d('app_users', 'Token') ?></th>
                            <th><?= __d('app_users', 'Token Secret') ?></th>
                            <th><?= __d('app_users', 'Token Expires') ?></th>
                            <th><?= __d('app_users', 'Data') ?></th>
                            <th><?= __d('app_users', 'Created') ?></th>
                            <th><?= __d('app_users', 'Modified') ?></th>
                            <th class="actions"><?= __d('app_users', 'Actions') ?></th>
                        </tr>
                        <?php foreach ($user->social_accounts as $socialAccounts) : ?>
                        <tr>
                            <td><?= h($socialAccounts->id) ?></td>
                            <td><?= h($socialAccounts->provider) ?></td>
                            <td><?= h($socialAccounts->username) ?></td>
                            <td><?= h($socialAccounts->active) ?></td>
                            <td><?= h($socialAccounts->reference) ?></td>
                            <td><?= h($socialAccounts->avatar) ?></td>
                            <td><?= h($socialAccounts->description) ?></td>
                            <td><?= h($socialAccounts->link) ?></td>
                            <td><?= h($socialAccounts->token) ?></td>
                            <td><?= h($socialAccounts->token_secret) ?></td>
                            <td><?= h($socialAccounts->token_expires) ?></td>
                            <td><?= h($socialAccounts->data) ?></td>
                            <td><?= h($socialAccounts->created) ?></td>
                            <td><?= h($socialAccounts->modified) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(
                                    __d('app_users', 'View'),
                                    ['controller' => 'SocialAccounts', 'action' => 'view', $socialAccounts->id]
                                ) ?>
                                <?= $this->Html->link(
                                    __d('app_users', 'Edit'),
                                    ['controller' => 'SocialAccounts', 'action' => 'edit', $socialAccounts->id]
                                ) ?>
                                <?= $this->Form->postLink(
                                    __d('app_users', 'Delete'),
                                    ['controller' => 'SocialAccounts', 'action' => 'delete', $socialAccounts->id],
                                    [
                                        'confirm' => __d(
                                            'app_users',
                                            'Are you sure you want to delete # {0}?',
                                            $socialAccounts->id
                                        ),
                                    ]
                                ) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
