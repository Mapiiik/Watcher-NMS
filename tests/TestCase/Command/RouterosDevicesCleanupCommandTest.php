<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use App\Command\RouterosDevicesCleanupCommand;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\I18n\DateTime;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * App\Command\RouterosDevicesCleanupCommand Test Case
 */
#[UsesClass(RouterosDevicesCleanupCommand::class)]
class RouterosDevicesCleanupCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.AccessPointTypes',
        'app.AccessPoints',
        'app.CustomerPoints',
        'app.CustomerConnections',
        'app.DeviceTypes',
        'app.RouterosDevices',
        'app.RouterosDeviceInterfaces',
        'app.RouterosDeviceIps',
    ];

    /**
     * What has not been heard from for a year goes, and what has stays.
     *
     * @return void
     */
    public function testWhatHasNotBeenHeardFromGoes(): void
    {
        $this->heardFrom('RouterosDevices', 10);
        $this->heardFrom('RouterosDeviceInterfaces', 400);
        $this->heardFrom('RouterosDeviceIps', 300);

        $this->exec('routeros_devices_cleanup');

        $this->assertExitSuccess();
        $this->assertSame(1, $this->left('RouterosDevices'));
        $this->assertSame(0, $this->left('RouterosDeviceInterfaces'));
        $this->assertSame(1, $this->left('RouterosDeviceIps'));
    }

    /**
     * How long is too long can be said for the run.
     *
     * @return void
     */
    public function testHowLongIsTooLongCanBeSaid(): void
    {
        $this->heardFrom('RouterosDevices', 10);
        $this->heardFrom('RouterosDeviceInterfaces', 10);
        $this->heardFrom('RouterosDeviceIps', 300);

        $this->exec('routeros_devices_cleanup --days 200');

        $this->assertExitSuccess();
        $this->assertSame(0, $this->left('RouterosDeviceIps'));
        $this->assertSame(1, $this->left('RouterosDeviceInterfaces'));
    }

    /**
     * Dates the records of a table back. Fixtures hold fixed days and the rule is counted from now.
     *
     * @param string $table Which table.
     * @param int $days How long ago.
     * @return void
     */
    private function heardFrom(string $table, int $days): void
    {
        $this->getTableLocator()->get($table)->updateAll(['modified' => DateTime::now()->subDays($days)], []);
    }

    /**
     * @param string $table Which table.
     * @return int How many records it still holds.
     */
    private function left(string $table): int
    {
        return $this->getTableLocator()->get($table)->find()->count();
    }
}
