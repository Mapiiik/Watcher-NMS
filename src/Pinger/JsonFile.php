<?php
declare(strict_types=1);

namespace App\Pinger;

use DateTimeInterface;

/**
 * The network written for Watcher Pinger: the tree of access points with their addresses.
 *
 * An access point names its parent, devices come before customers, and what an address belongs to
 * is said by its kind, with the manufacturer of a radio unit where it is known.
 */
final class JsonFile
{
    /**
     * Raised when a reader of the previous version could no longer read the file.
     */
    public const VERSION = 1;

    /**
     * The file.
     *
     * @param \App\Pinger\Network $network The network.
     * @param \DateTimeInterface $generated When the data were read.
     * @return string
     */
    public function render(Network $network, DateTimeInterface $generated): string
    {
        $accessPoints = [];
        foreach ($network->accessPoints as $accessPoint) {
            $accessPoints[] = [
                'id' => $accessPoint->id,
                'name' => $accessPoint->name,
                'parentId' => $accessPoint->parentId,
                'hosts' => array_map($this->host(...), [...$accessPoint->devices, ...$accessPoint->customers]),
            ];
        }

        return json_encode(
            [
                'format' => 'watcher-pinger',
                'version' => self::VERSION,
                'generated' => $generated->format(DateTimeInterface::ATOM),
                'accessPoints' => $accessPoints,
                'unassigned' => array_map($this->host(...), $network->unassigned),
            ],
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        );
    }

    /**
     * One address.
     *
     * @param \App\Pinger\Host $host The address.
     * @return array<string, mixed>
     */
    private function host(Host $host): array
    {
        return [
            'name' => $host->name,
            'ip' => $host->ipAddress,
            'kind' => $host->kind?->value,
            'manufacturer' => $host->manufacturer,
            'contact' => $host->isAccessPointContact,
        ];
    }
}
