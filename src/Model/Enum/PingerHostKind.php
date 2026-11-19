<?php
declare(strict_types=1);

namespace App\Model\Enum;

use App\Model\Enum\Trait\EnumOptionsTrait;
use Cake\Database\Type\EnumLabelInterface;
use Override;

/**
 * PingerHostKind Enum
 *
 * What an address handed out to be pinged belongs to.
 *
 * @see \App\Pinger\AddressBook
 */
enum PingerHostKind: string implements EnumLabelInterface
{
    use EnumOptionsTrait;

    case RadioUnit = 'radio-unit';
    case RouterosDevice = 'routeros-device';
    case PowerSupply = 'power-supply';
    case Customer = 'customer';

    /**
     * @return string
     */
    #[Override]
    public function label(): string
    {
        return match ($this) {
            self::RadioUnit => __('Radio Unit'),
            self::RouterosDevice => __('RouterOS Device'),
            self::PowerSupply => __('Power Supply'),
            self::Customer => __('Customer'),
        };
    }
}
