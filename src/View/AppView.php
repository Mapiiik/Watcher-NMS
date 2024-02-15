<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.0.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\View;

use Cake\View\View;
use IntlDateFormatter;

/**
 * Application View
 *
 * Your application's default view class
 *
 * @link https://book.cakephp.org/5/en/views.html#the-app-view
 * @property \CakeDC\Users\View\Helper\AuthLinkHelper $AuthLink
 * @property \Geo\View\Helper\GoogleMapHelper $GoogleMap
 */
class AppView extends View
{
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like adding helpers.
     *
     * e.g. `$this->addHelper('Html');`
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->addHelper('CakeDC/Users.User');
        $this->addHelper('CakeDC/Users.AuthLink');
    }

    /**
     * Months method
     *
     * @return array<string> Months names
     */
    public static function months(): array
    {
        $formatter = new IntlDateFormatter(null, IntlDateFormatter::FULL, IntlDateFormatter::FULL);
        $formatter->setPattern('LLLL');
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = $formatter->format(mktime(0, 0, 0, $m, 12));
        }

        return $months;
    }
}
