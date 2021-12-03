<div class="container nav-container">
    <div class="content nav-content top-nav">
        <div class="nav-content-left">
            <?= $this->AuthLink->link(
                '<h4>' . __('Access Point') . ': ' . h($accessPoint->name) . '</h4>',
                ['plugin' => null, 'controller' => 'AccessPoints', 'action' => 'view', $accessPoint->id], ['escape' => false, 'class' => ''])
            ?>
        </div>
        <div class="nav-content-right">
            <?= $this->request->getParam('controller') <> 'AccessPoints' ?
                $this->AuthLink->link(
                    'X',
                    ['access_point_id' => false, '?' => $this->request->getQueryParams()] + $this->request->getParam('pass'),
                    ['class' => 'button button-small']
                ) :
                ''
            ?>
        </div>
    </div>
</div>
<br />