<?php
declare(strict_types=1);

namespace App\Model\Enum;

use App\Model\Enum\Trait\EnumOptionsTrait;
use Cake\Database\Type\EnumLabelInterface;
use Override;

/**
 * PingerFormat Enum
 *
 * What the network devices and customer addresses are handed out as, one for each program that
 * pings them.
 *
 * @see \App\Controller\AccessPointsController::pinger()
 */
enum PingerFormat: string implements EnumLabelInterface
{
    use EnumOptionsTrait;

    case Json = 'json';
    case Adr = 'adr';

    /**
     * The program that reads it.
     *
     * @return string
     */
    #[Override]
    public function label(): string
    {
        return match ($this) {
            self::Json => 'Watcher Pinger',
            self::Adr => "McLEI's Pinger",
        };
    }

    /**
     * What it holds.
     *
     * @return string
     */
    public function description(): string
    {
        return match ($this) {
            self::Json => __('The tree of access points with their devices and customers'),
            self::Adr => __('An address book with a tab for each tree of access points'),
        };
    }

    /**
     * Where the program is to be had, if anywhere.
     *
     * @return string|null
     */
    public function url(): ?string
    {
        return match ($this) {
            self::Json => 'https://github.com/Mapiiik/Watcher-Pinger',
            self::Adr => null,
        };
    }

    /**
     * What the file is called.
     *
     * @return string
     */
    public function fileName(): string
    {
        return match ($this) {
            self::Json => 'watcher-pinger.json',
            self::Adr => 'nms.adr',
        };
    }
}
