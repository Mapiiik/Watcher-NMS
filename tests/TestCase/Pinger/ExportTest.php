<?php
declare(strict_types=1);

namespace App\Test\TestCase\Pinger;

use App\Pinger\Export;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use Override;

/**
 * The network handed out, kept for a while.
 */
class ExportTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.AccessPointTypes',
        'app.AccessPoints',
        'app.AccessPointContacts',
        'app.Manufacturers',
        'app.RadioUnitBands',
        'app.AntennaTypes',
        'app.RadioLinks',
        'app.RadioUnitTypes',
        'app.RadioUnits',
        'app.PowerSupplyTypes',
        'app.PowerSupplies',
        'app.CustomerPoints',
        'app.CustomerConnections',
        'app.CustomerConnectionIps',
        'app.DeviceTypes',
        'app.RouterosDevices',
        'app.RouterosDeviceInterfaces',
        'app.RouterosDeviceIps',
    ];

    /**
     * @inheritDoc
     */
    #[Override]
    protected function tearDown(): void
    {
        Configure::write('Pinger.cacheSeconds', 0);
        Cache::pool('default')->delete('pinger_export_json');

        parent::tearDown();
    }

    /**
     * Within the time kept the network is not put together again, so a change shows only after it;
     * with nothing kept every call puts it together.
     *
     * @return void
     * @link \App\Pinger\Export::json()
     */
    public function testTheNetworkIsKeptForAWhile(): void
    {
        Configure::write('Pinger.cacheSeconds', 60);
        Cache::pool('default')->delete('pinger_export_json');

        $first = (new Export())->json();
        $this->addAccessPoint('Pinger Export Later');
        $kept = (new Export())->json();
        $this->assertSame($first, $kept);
        $this->assertStringNotContainsString('Pinger Export Later', $kept['body']);

        Configure::write('Pinger.cacheSeconds', 0);
        $this->assertStringContainsString('Pinger Export Later', (new Export())->json()['body']);
    }

    /**
     * Add an access point.
     *
     * @param string $name What it is called.
     * @return void
     */
    private function addAccessPoint(string $name): void
    {
        $table = $this->getTableLocator()->get('AccessPoints');
        $table->saveOrFail($table->newEntity(['name' => $name]));
    }
}
