<?php
/**
 * The filter every listing of what the agents write carries: how old the last reading may be.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Enum\MaximumAge $maximumAge What the listing was answered with, which is what the
 *   form is to show rather than what the address asked for.
 */

use App\Model\Enum\MaximumAge;

echo $this->Form->control('maximum_age', [
    'label' => __('Maximum Age'),
    'type' => 'select',
    'options' => MaximumAge::options(),
    'value' => $maximumAge->value,
    'onchange' => $this::SUBMIT_ON_CHANGE,
]);
