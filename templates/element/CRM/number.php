<?php
/**
 * A customer or a contract number, leading to the other application.
 *
 * The number is all this application keeps of either, so the link asks that application to search
 * for it rather than pointing at a record this one cannot name. Where there is no such application
 * to point at, the number is written plainly - which is what it is either way.
 *
 * @var \Cake\View\View $this
 * @var string|null $number
 */

use App\CRM\Links;

if ($number === null || $number === '') {
    return;
}

$url = Links::search($number);

echo $url !== null
    ? $this->Html->link($number, $url, ['target' => '_blank'])
    : h($number);
