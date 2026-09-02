<?php
declare(strict_types=1);

namespace App\Model\Enum;

use App\CRM\Tasks as CrmTasks;
use Cake\Database\Type\EnumLabelInterface;
use Override;

/**
 * The page a user arrives at when they open the application.
 *
 * The dashboard is what most people want to see first, and for some it is a page they click
 * past every morning on the way to the one they actually work in. What is on offer here is the
 * top navigation, no more: a landing page has to be somewhere the user can also get back to,
 * and it is checked against their permissions before they are sent there.
 */
enum LandingPage: string implements EnumLabelInterface
{
    case Dashboard = 'dashboard';
    case AccessPoints = 'access-points';
    case Tasks = 'tasks';
    case CustomerPoints = 'customer-points';
    case IpAddressRanges = 'ip-address-ranges';
    case RouterosDevices = 'routeros-devices';
    case RadioLinks = 'radio-links';
    case PowerSupplies = 'power-supplies';
    case Overviews = 'overviews';
    case Settings = 'settings';

    /**
     * @return string
     */
    #[Override]
    public function label(): string
    {
        return match ($this) {
            self::Dashboard => __('Dashboard'),
            self::AccessPoints => __('Access Points'),
            self::Tasks => __('Tasks'),
            self::CustomerPoints => __('Customer Points'),
            self::IpAddressRanges => __('IP Address Ranges'),
            self::RouterosDevices => __('RouterOS Devices'),
            self::RadioLinks => __('Radio Links'),
            self::PowerSupplies => __('Power Supplies'),
            self::Overviews => __('Overviews'),
            self::Settings => __('Settings'),
        };
    }

    /**
     * Where the page lives, as the navigation names it.
     *
     * The nesting a URL is built under is left alone: this is only ever built on the root,
     * where there is no access point for the URL filter to carry over.
     *
     * @return array<string, mixed>
     */
    public function url(): array
    {
        return match ($this) {
            self::Dashboard => ['plugin' => null, 'controller' => 'Dashboard', 'action' => 'index'],
            self::AccessPoints => ['plugin' => null, 'controller' => 'AccessPoints', 'action' => 'index'],
            self::Tasks => ['plugin' => null, 'controller' => 'Tasks', 'action' => 'index'],
            self::CustomerPoints => ['plugin' => null, 'controller' => 'CustomerPoints', 'action' => 'index'],
            self::IpAddressRanges => ['plugin' => null, 'controller' => 'IpAddressRanges', 'action' => 'index'],
            self::RouterosDevices => ['plugin' => null, 'controller' => 'RouterosDevices', 'action' => 'index'],
            self::RadioLinks => ['plugin' => null, 'controller' => 'RadioLinks', 'action' => 'index'],
            self::PowerSupplies => ['plugin' => null, 'controller' => 'PowerSupplies', 'action' => 'index'],
            self::Overviews => ['plugin' => null, 'controller' => 'Overviews', 'action' => 'index'],
            self::Settings => ['plugin' => null, 'controller' => 'Settings', 'action' => 'index'],
        };
    }

    /**
     * The pages on offer, as stored value to what the page is called.
     *
     * Written out rather than taken from EnumOptionsTrait, because where the tasks are the
     * other application's this one does not offer them - the navigation leaves the item out,
     * and a landing page nothing leads back to is somewhere to be stranded. The permissions
     * know nothing of that arrangement, so it has to be said here.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            if ($case === self::Tasks && CrmTasks::areUsed()) {
                continue;
            }

            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
