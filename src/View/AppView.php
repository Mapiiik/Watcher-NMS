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
use IntlCalendar;
use IntlDateFormatter;
use Override;
use RuntimeException;

/**
 * Application View
 *
 * Your application's default view class
 *
 * @link https://book.cakephp.org/5/en/views.html#the-app-view
 * @property \CakeDC\Users\View\Helper\AuthLinkHelper $AuthLink
 * @property \Geo\View\Helper\GoogleMapHelper $GoogleMap
 * @property \Geo\View\Helper\LeafletHelper $Leaflet
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
    #[Override]
    public function initialize(): void
    {
        parent::initialize();
        $this->addHelper('CakeDC/Users.User');
        $this->addHelper('CakeDC/Users.AuthLink');
    }

    /**
     * Months method
     *
     * @return array<int, string> Months names
     */
    public static function months(): array
    {
        $formatter = new IntlDateFormatter(
            locale: null,
            dateType: IntlDateFormatter::FULL,
            timeType: IntlDateFormatter::NONE,
        );

        $formatter->setPattern('LLLL');

        $calendar = IntlCalendar::createInstance();

        $months = [];

        for ($m = 1; $m <= 12; $m++) {
            $calendar->set(
                IntlCalendar::FIELD_MONTH,
                $m - 1, // IntlCalendar months are 0-based
            );

            $month = $formatter->format($calendar);

            if ($month === false) {
                throw new RuntimeException('Failed to format month name: ' . $formatter->getErrorMessage());
            }
            $months[$m] = $month;
        }

        return $months;
    }
}
