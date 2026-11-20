<?php
declare(strict_types=1);

namespace App\Pinger;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\I18n\DateTime;

/**
 * The network as it is handed out to the programs that ping it, kept for a while once put together.
 *
 * Putting it together reads every active customer connection, which takes a few tenths of a second
 * and tens of megabytes; a technician clicking twice, or a client asking in a loop, then costs that
 * once (`Pinger.cacheSeconds`, 0 for never kept).
 */
final class Export
{
    /**
     * The file of Watcher Pinger.
     *
     * @return array{body: string, addresses: int}
     */
    public function json(): array
    {
        return $this->remembered('json', function (): array {
            $network = (new AddressBook())->network();
            $addresses = count($network->unassigned);
            foreach ($network->accessPoints as $accessPoint) {
                $addresses += count($accessPoint->devices) + count($accessPoint->customers);
            }

            return [
                'body' => (new JsonFile())->render($network, DateTime::now()),
                'addresses' => $addresses,
            ];
        });
    }

    /**
     * The address book of McLEI's Pinger in the code page given.
     *
     * @param string $charset The code page.
     * @return array{body: string, addresses: int}
     */
    public function adr(string $charset): array
    {
        return $this->remembered('adr_' . $charset, function () use ($charset): array {
            $tabs = (new AddressBook())->build();
            $addresses = 0;
            foreach ($tabs as $tab) {
                foreach ($tab->blocks as $block) {
                    $addresses += count($block->hosts);
                }
            }

            return ['body' => (new AdrFile($charset))->render($tabs), 'addresses' => $addresses];
        });
    }

    /**
     * What was put together lately, or what make puts together now and keeps.
     *
     * @param string $name What it is.
     * @param callable(): array{body: string, addresses: int} $make Puts it together.
     * @return array{body: string, addresses: int}
     */
    private function remembered(string $name, callable $make): array
    {
        $seconds = (int)Configure::read('Pinger.cacheSeconds', 120);
        if ($seconds <= 0) {
            return $make();
        }

        $key = 'pinger_export_' . preg_replace('/[^a-z0-9_]/i', '_', $name);
        $cache = Cache::pool('default');
        $kept = $cache->get($key);
        if (is_array($kept) && isset($kept['body'], $kept['addresses'])) {
            /** @var array{body: string, addresses: int} $kept */
            return $kept;
        }

        $made = $make();
        $cache->set($key, $made, $seconds);

        return $made;
    }
}
