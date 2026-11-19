<?php
declare(strict_types=1);

namespace App\Test\TestCase\Pinger;

use App\Model\Enum\PingerHostKind;
use App\Pinger\AccessPointAddresses;
use App\Pinger\Host;
use App\Pinger\JsonFile;
use App\Pinger\Network;
use Cake\I18n\DateTime;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Pinger\JsonFile Test Case
 */
#[UsesClass(JsonFile::class)]
class JsonFileTest extends TestCase
{
    /**
     * An access point names its parent and lists its devices before its customers, each address
     * with what it belongs to. Customers of no access point are listed apart.
     *
     * @return void
     * @link \App\Pinger\JsonFile::render()
     */
    public function testTheTreeIsWrittenWithItsAddresses(): void
    {
        $network = new Network(
            [
                new AccessPointAddresses('root-id', 'Root', null, [], []),
                new AccessPointAddresses(
                    'child-id',
                    'Child Metyšova',
                    'root-id',
                    [new Host('Child - Root - IN', '10.9.1.243', false, PingerHostKind::RadioUnit, 'ACME Radio')],
                    [new Host('Landlord (C-1)', '192.0.2.21', true, PingerHostKind::Customer)],
                ),
            ],
            [new Host('Nowhere (C-2)', '192.0.2.27', false, PingerHostKind::Customer)],
        );

        $content = (new JsonFile())->render($network, new DateTime('2026-11-19 16:00:00+01:00'));

        $this->assertSame(
            [
                'format' => 'watcher-pinger',
                'version' => 1,
                'generated' => '2026-11-19T16:00:00+01:00',
                'accessPoints' => [
                    ['id' => 'root-id', 'name' => 'Root', 'parentId' => null, 'hosts' => []],
                    [
                        'id' => 'child-id',
                        'name' => 'Child Metyšova',
                        'parentId' => 'root-id',
                        'hosts' => [
                            [
                                'name' => 'Child - Root - IN',
                                'ip' => '10.9.1.243',
                                'kind' => 'radio-unit',
                                'manufacturer' => 'ACME Radio',
                                'contact' => false,
                            ],
                            [
                                'name' => 'Landlord (C-1)',
                                'ip' => '192.0.2.21',
                                'kind' => 'customer',
                                'manufacturer' => null,
                                'contact' => true,
                            ],
                        ],
                    ],
                ],
                'unassigned' => [
                    [
                        'name' => 'Nowhere (C-2)',
                        'ip' => '192.0.2.27',
                        'kind' => 'customer',
                        'manufacturer' => null,
                        'contact' => false,
                    ],
                ],
            ],
            json_decode($content, true, flags: JSON_THROW_ON_ERROR),
        );
        $this->assertStringContainsString('Metyšova', $content, 'letters are written as they are');
    }
}
