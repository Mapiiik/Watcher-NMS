<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\TestWith;

/**
 * What the network columns of the database refuse is told beside the field, not by an error page.
 */
class NetworkColumnsTest extends TestCase
{
    use LocatorAwareTrait;

    /**
     * @param string $table The table.
     * @param string $column The column.
     * @param string $wrong What the database would refuse.
     * @param string $right What it takes.
     * @return void
     */
    #[TestWith(['IpAddressRanges', 'ip_network', '192.0.2.1/24', '192.0.2.0/24'])]
    #[TestWith(['IpAddressRanges', 'ip_gateway', '10.0.0.256', '192.0.2.1'])]
    #[TestWith(['RadioUnits', 'ip_address', '10.0.0.256', '192.0.2.1'])]
    #[TestWith(['RouterosDevices', 'ip_address', '10.0.0.256', '192.0.2.1'])]
    #[TestWith(['RouterosDeviceIps', 'ip_address', '10.0.0.256', '192.0.2.1/24'])]
    #[TestWith(['CustomerConnectionIps', 'ip_address', '10.0.0.256', '192.0.2.1'])]
    #[TestWith(['RadarInterferences', 'mac_address', '11:22:33:44:55', '11:22:33:44:55:66'])]
    #[TestWith(['RouterosDeviceInterfaces', 'mac_address', '11:22:33:44:55', '11:22:33:44:55:66'])]
    #[TestWith(['RouterosDeviceInterfaces', 'bssid', '11:22:33:44:55', '11:22:33:44:55:66'])]
    public function testAColumnTakesWhatTheDatabaseTakes(string $table, string $column, string $wrong, string $right): void
    {
        $columns = $this->fetchTable($table);

        $this->assertNotEmpty($columns->newEntity([$column => $wrong])->getError($column));
        $this->assertEmpty($columns->newEntity([$column => $right])->getError($column));
    }
}
