<?php
/**
 * What is said about a folder.
 *
 * What it hangs on is offered only where the address did not already say it: under an access point
 * the route settles it, and the field would be a way of filing the folder against another one.
 * From the shelf itself there is nothing settling it, so it is asked for.
 *
 * @var \App\View\AppView $this
 * @var array<string, string> $kinds
 * @var \Cake\Collection\CollectionInterface<string, string>|array<string>|null $accessPoints
 */
?>
<?php
if (isset($accessPoints)) {
    echo $this->Form->control('access_point_id', [
        'label' => __d('app_files', 'Access Point'),
        'options' => $accessPoints,
        'empty' => true,
    ]);
}
echo $this->Form->control('documentation_type_id', [
    'label' => __d('app_files', 'Documentation Type'),
    'options' => $kinds,
    'empty' => true,
]);
echo $this->Form->control('happened_on', [
    'label' => __d('app_files', 'Happened On'),
    'empty' => true,
    'title' => __d(
        'app_files',
        'The day it is about. Documentation kept up to date rather than recording something'
        . ' has none, and goes by its name instead.',
    ),
]);
echo $this->Form->control('name', [
    'label' => __d('app_files', 'Name'),
    'title' => __d('app_files', 'Left empty, it reads as its day and its type.'),
]);
echo $this->Form->control('note', ['label' => __d('app_files', 'Note')]);
