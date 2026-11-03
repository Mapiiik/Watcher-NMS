<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

/**
 * @psalm-suppress UnnecessaryVarAnnotation
 * @var \App\View\AppView $this
 * @psalm-scope-this \App\View\AppView
 */

use Cake\Core\Configure;

$cakeDescription = 'Watcher NMS | ' . (string)Configure::read('App.company');
?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?= $cakeDescription ?> |
        <?= $this->fetch('title') ?>
    </title>
    <?php // the ICO is for those that cannot take the SVG, and its size keeps those that can on the SVG ?>
    <?= $this->Html->meta('icon', 'favicon.ico', ['sizes' => '32x32']) ?>
    <?= $this->Html->meta('icon', 'favicon.svg', ['type' => 'image/svg+xml']) ?>
    <?= $this->Html->meta(['rel' => 'apple-touch-icon', 'link' => 'apple-touch-icon.png']) ?>

    <?= $this->Html->css([
        '/vendor/normalize/normalize.min.css',
        '/vendor/milligram/milligram.min.css',
        'cake',
    ]) ?>
    
    <?= $this->Html->script('/vendor/jquery/jquery.min.js') ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body>
    <nav class="top-nav">
        <div class="top-nav-title">
            <a href="/"><?=
                $this->Html->image('/favicon.svg', ['alt' => '', 'class' => 'top-nav-logo'])
            ?><span>Watcher</span> NMS</a>
        </div>
        <div class="top-nav-links">
        </div>
    </nav>
    <main class="main">
        <div class="container">
            <?= $this->Flash->render() ?>
            <?= $this->fetch('content') ?>
        </div>
    </main>
    <footer>
    </footer>
</body>
</html>
