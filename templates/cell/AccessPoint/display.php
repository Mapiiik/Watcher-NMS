<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AccessPoint $accessPoint
 * @var bool $compact
 */
?>
<div class="container nav-container">
    <div class="content nav-content top-nav">
        <div class="nav-content-left">
            <h4><?= $this->AuthLink->link(
                __('Access Point') . ': ' . h($accessPoint->name),
                ['plugin' => null, 'controller' => 'AccessPoints', 'action' => 'view', $accessPoint->id],
                ['escape' => false, 'class' => '']
            ) ?></h4>
        </div>
        <div class="nav-content-right">
            <?= $this->getRequest()->getParam('controller') <> 'AccessPoints' ?
                $this->AuthLink->link(
                    'X',
                    [
                        'access_point_id' => false,
                        '?' => $this->getRequest()->getQueryParams(),
                    ] + $this->getRequest()->getParam('pass'),
                    ['class' => 'button button-small']
                ) :
                ''
            ?>
        </div>
    </div>
</div>
<br />